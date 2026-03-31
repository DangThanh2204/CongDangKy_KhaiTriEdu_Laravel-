<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $conversation = $this->resolveConversation($request);
        $conversation->messages()->create([
            'role' => 'user',
            'message' => $data['message'],
            'meta' => [
                'current_url' => $data['current_url'] ?? null,
                'page_title' => $data['page_title'] ?? null,
            ],
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

        $result = $this->assistant->chat(
            $data['message'],
            null,
            [
                'current_url' => $data['current_url'] ?? null,
                'page_title' => $data['page_title'] ?? null,
                'history' => $history,
            ]
        );

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Không thể trả lời lúc này.',
            ], 500);
        }

        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'message' => $result['message'],
            'recommended_courses' => $result['recommended_courses'] ?? [],
            'meta' => [
                'source' => $result['source'] ?? 'assistant',
                'status' => $result['status'] ?? null,
            ],
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => $assistantMessage->message,
            'recommended_courses' => $assistantMessage->recommended_courses ?? [],
            'source' => $assistantMessage->meta['source'] ?? 'assistant',
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