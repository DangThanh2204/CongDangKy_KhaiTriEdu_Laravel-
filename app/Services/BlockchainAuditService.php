<?php

namespace App\Services;

use Illuminate\Support\Str;

class BlockchainAuditService
{
    public function __construct(protected FireflyService $firefly)
    {
    }

    public function record(string $action, array $payload = [], array $context = []): array
    {
        if (! $this->firefly->isConfigured()) {
            return [
                'success' => false,
                'message' => 'FireFly not configured',
                'action' => $action,
            ];
        }

        $requestId = (string) ($context['request_id'] ?? $context['reference'] ?? Str::uuid());

        $envelope = [
            'action' => $action,
            'app' => config('app.name', 'Khai Tri Edu'),
            'recorded_at' => now()->toIso8601String(),
            'reference' => $context['reference'] ?? null,
            'actor' => [
                'user_id' => $context['user_id'] ?? null,
                'username' => $context['username'] ?? null,
                'role' => $context['role'] ?? null,
                'ip' => $context['ip'] ?? null,
            ],
            'payload' => $payload,
        ];

        return $this->firefly->broadcastAuditEvent($action, $envelope, [
            'request_id' => $requestId,
        ]);
    }
}