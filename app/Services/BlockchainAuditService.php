<?php

namespace App\Services;

use Illuminate\Support\Str;

class BlockchainAuditService
{
    public function __construct(protected FireflyConsortiumService $consortium)
    {
    }

    public function record(string $action, array $payload = [], array $context = []): array
    {
        if (! $this->consortium->isConfigured()) {
            return [
                'success' => false,
                'message' => 'FireFly chưa được cấu hình.',
                'action' => $action,
            ];
        }

        $requestId = (string) ($context['request_id'] ?? $context['reference'] ?? Str::uuid());
        $consortium = $this->consortium->summary();

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
            'consortium' => [
                'required_quorum' => $consortium['required_quorum'] ?? 1,
                'members_total' => $consortium['configured_members'] ?? 0,
                'members' => collect($consortium['members'] ?? [])
                    ->map(fn (array $member) => [
                        'key' => $member['key'] ?? null,
                        'label' => $member['label'] ?? null,
                        'role' => $member['role'] ?? null,
                        'endpoint' => $member['endpoint'] ?? null,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        return $this->consortium->broadcastAuditEvent($action, $envelope, [
            'request_id' => $requestId,
        ]);
    }
}
