<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    public function __construct(protected GeminiChatService $assistant)
    {
    }

    public function history(Request $request): JsonResponse
    {
        $conversation = $this->findConversation($request);

        return response()->json([
            'success' => true,
            'messages' => $conversation
                ? $conversation->messages()
                    ->latest('id')
                    ->take(20)
                    ->get()
                    ->sortBy('id')
                    ->values()
                    ->map(fn (AssistantMessage $message) => $this->transformMessage($message))
                    ->all()
                : [],
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'current_url' => 'nullable|string|max:500',
            'page_title' => 'nullable|string|max:255',
        ]);

        $meta = [
            'current_url' => $data['current_url'] ?? null,
            'page_title' => $data['page_title'] ?? null,
        ];

        $conversation = null;
        $history = [];

        try {
            $conversation = $this->resolveConversation($request);
            $conversation->messages()->create([
                'role' => 'user',
                'message' => $data['message'],
                'meta' => $meta,
            ]);

            $history = $conversation->messages()
                ->latest('id')
                ->take(12)
                ->get()
                ->sortBy('id')
                ->values()
                ->map(fn (AssistantMessage $message) => [
                    'role' => $message->role,
                    'message' => $message->message,
                ])
                ->all();
        } catch (\Throwable $exception) {
            Log::error('AssistantController failed to persist user message.', [
                'message' => $exception->getMessage(),
                'class' => get_class($exception),
            ]);
        }

        try {
            $result = $this->assistant->chat(
                $data['message'],
                null,
                $meta + ['history' => $history],
            );
        } catch (\Throwable $exception) {
            Log::error('AssistantController: assistant service threw.', [
                'message' => $exception->getMessage(),
                'class' => get_class($exception),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Trợ lý đang gặp sự cố tạm thời. Bạn vui lòng thử lại sau ít phút hoặc liên hệ trung tâm để được hỗ trợ.',
                'recommended_courses' => [],
                'source' => 'fallback_exception',
            ]);
        }

        $assistantText = $result['message'] ?? 'Mình chưa thể trả lời ngay lúc này, bạn thử lại sau ít phút nhé.';
        $recommended = $result['recommended_courses'] ?? [];
        $source = $result['source'] ?? 'assistant';

        if ($conversation) {
            try {
                $conversation->messages()->create([
                    'role' => 'assistant',
                    'message' => $assistantText,
                    'recommended_courses' => $recommended,
                    'meta' => [
                        'source' => $source,
                        'status' => $result['status'] ?? null,
                    ],
                ]);

                $conversation->forceFill([
                    'last_message_at' => now(),
                ])->save();
            } catch (\Throwable $exception) {
                Log::error('AssistantController failed to persist assistant message.', [
                    'message' => $exception->getMessage(),
                    'class' => get_class($exception),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $assistantText,
            'recommended_courses' => $recommended,
            'source' => $source,
        ]);
    }

    protected function resolveConversation(Request $request): AssistantConversation
    {
        $request->session()->start();

        return AssistantConversation::query()->firstOrCreate(
            [
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
            ],
            [
                'started_at' => now(),
                'last_message_at' => now(),
            ]
        );
    }

    protected function findConversation(Request $request): ?AssistantConversation
    {
        $request->session()->start();

        return AssistantConversation::query()
            ->where('user_id', $request->user()?->id)
            ->where('session_id', $request->session()->getId())
            ->first();
    }

    protected function transformMessage(AssistantMessage $message): array
    {
        return [
            'role' => $message->role,
            'message' => $message->message,
            'recommended_courses' => $message->recommended_courses ?? [],
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }
}