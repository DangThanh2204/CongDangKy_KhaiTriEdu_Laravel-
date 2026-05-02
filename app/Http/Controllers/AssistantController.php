<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\Course;
use App\Models\Setting;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        Log::info('AssistantController::chat invoked', [
            'has_body' => $request->isJson() || $request->isMethod('POST'),
            'session_started' => $request->hasSession(),
        ]);

        try {
            $data = $request->validate([
                'message' => 'required|string|max:2000',
                'current_url' => 'nullable|string|max:500',
                'page_title' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('AssistantController::chat validation crashed.', [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            return $this->fallbackJson('Yêu cầu chưa hợp lệ, bạn thử nhập lại nhé.', 'fallback_validation');
        }

        return $this->runChat($request, $data);
    }

    protected function runChat(Request $request, array $data): JsonResponse
    {
        try {
            return $this->runChatInner($request, $data);
        } catch (\Throwable $exception) {
            Log::error('AssistantController::chat unhandled exception.', [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->fallbackJson(
                'Trợ lý đang gặp sự cố tạm thời, bạn thử lại sau ít phút giúp mình nhé.',
                'fallback_unhandled'
            );
        }
    }

    protected function runChatInner(Request $request, array $data): JsonResponse
    {
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

    public function health(): JsonResponse
    {
        $checks = [
            'config' => [
                'has_api_key' => filled(config('services.gemini.api_key')),
                'model' => config('services.gemini.assistant_model'),
                'base_url' => config('services.gemini.base_url'),
                'guides_count' => count((array) config('assistant_guides.guides', [])),
            ],
        ];

        $checks['setting_query'] = $this->probe(fn () => [
            'value_type' => gettype(Setting::get('ai_assistant_prompt', '')),
        ]);

        $checks['course_query'] = $this->probe(fn () => [
            'count_published' => Course::query()->where('status', 'published')->count(),
        ]);

        $checks['conversation_query'] = $this->probe(fn () => [
            'count' => AssistantConversation::query()->count(),
        ]);

        $checks['service_boot'] = $this->probe(fn () => [
            'configured' => app(GeminiChatService::class)->isConfigured(),
        ]);

        return response()->json($checks);
    }

    protected function probe(\Closure $callback): array
    {
        try {
            return ['ok' => true] + $callback();
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ];
        }
    }

    protected function fallbackJson(string $message, string $source): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'recommended_courses' => [],
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