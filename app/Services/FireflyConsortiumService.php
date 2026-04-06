<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FireflyConsortiumService
{
    protected array $members;
    protected int $requiredQuorum;

    public function __construct()
    {
        $this->members = $this->buildMembers();
        $this->requiredQuorum = $this->determineQuorum();
    }

    public function members(): array
    {
        return $this->members;
    }

    public function requiredQuorum(): int
    {
        return $this->requiredQuorum;
    }

    public function isConfigured(): bool
    {
        return count($this->configuredMembers()) > 0;
    }

    public function consortiumEnabled(): bool
    {
        return count($this->configuredMembers()) > 1;
    }

    public function summary(): array
    {
        $members = [];
        $configuredMembers = 0;
        $healthyMembers = 0;

        foreach ($this->members as $member) {
            $status = $this->checkMember($member);
            $members[] = $status;

            if ($status['configured']) {
                $configuredMembers++;
            }

            if ($status['success']) {
                $healthyMembers++;
            }
        }

        $primary = $members[0] ?? null;
        $requiredQuorum = $this->requiredQuorum();
        $success = $healthyMembers >= max(1, $requiredQuorum);

        return [
            'success' => $success,
            'message' => $success
                ? 'Consortium FireFly đã sẵn sàng.'
                : ($configuredMembers === 0
                    ? 'Chưa có thành viên FireFly nào được cấu hình.'
                    : 'Chưa đủ số thành viên FireFly kết nối thành công để đạt quorum.'),
            'consortium_enabled' => $this->consortiumEnabled(),
            'required_quorum' => $requiredQuorum,
            'members_total' => count($members),
            'configured_members' => $configuredMembers,
            'healthy_members' => $healthyMembers,
            'members' => $members,
            'primary' => $primary,
            'endpoint' => $primary['endpoint'] ?? null,
            'namespace' => $primary['namespace'] ?? null,
            'platform_identity' => $primary['platform_identity'] ?? 'platform',
            'token_ready' => (bool) ($primary['token_ready'] ?? false),
        ];
    }

    public function broadcastAuditEvent(string $eventType, array $payload, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'FireFly chưa được cấu hình.',
            ];
        }

        $requestId = (string) ($context['request_id'] ?? Str::uuid());
        $results = [];
        $successCount = 0;
        $members = $this->configuredMembers();

        foreach ($members as $member) {
            $message = [
                'event' => $eventType,
                'topic' => $member['audit_topic'] ?: 'audit',
                'payload' => $payload,
                'consortium' => [
                    'member_key' => $member['key'],
                    'member_label' => $member['label'],
                    'member_role' => $member['role'],
                    'required_quorum' => $this->requiredQuorum(),
                    'members_total' => count($members),
                ],
            ];

            try {
                $response = $this->client($member)->post("/api/v1/namespaces/{$member['namespace']}/messages/broadcast", [
                    'idempotencyKey' => $requestId,
                    'data' => [[
                        'value' => json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]],
                ]);

                $result = $this->formatResponse($response, $member, $requestId);
            } catch (\Throwable $exception) {
                $result = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'member_key' => $member['key'],
                    'member_label' => $member['label'],
                    'member_role' => $member['role'],
                    'endpoint' => $member['base_url'],
                ];
            }

            if ($result['success']) {
                $successCount++;
            }

            $results[$member['key']] = $result;
        }

        $requiredQuorum = $this->requiredQuorum();
        $primarySuccess = collect($results)->first(static fn (array $result) => $result['success'] ?? false);

        return [
            'success' => $successCount >= max(1, $requiredQuorum),
            'message' => $successCount >= max(1, $requiredQuorum)
                ? 'Đã ghi nhận proof blockchain theo mô hình consortium.'
                : 'Chưa đạt quorum proof trên các thành viên FireFly.',
            'request_id' => $requestId,
            'success_count' => $successCount,
            'required_quorum' => $requiredQuorum,
            'members_total' => count($results),
            'member_results' => $results,
            'message_id' => data_get($primarySuccess, 'message_id'),
            'tx_id' => data_get($primarySuccess, 'tx_id'),
            'state' => data_get($primarySuccess, 'state'),
        ];
    }

    protected function buildMembers(): array
    {
        $configured = $this->decodeMembers(config('services.firefly.consortium_members'));
        $members = [];

        if ($configured !== []) {
            foreach (array_values($configured) as $index => $member) {
                if (! is_array($member)) {
                    continue;
                }

                $normalized = $this->normalizeMember($member, $index);

                if ($normalized['enabled']) {
                    $members[] = $normalized;
                }
            }
        }

        if ($members === []) {
            $fallback = $this->normalizeMember([
                'key' => 'khai-tri',
                'label' => config('services.firefly.member_label', config('app.name', 'Khai Tri Edu')),
                'role' => config('services.firefly.member_role', 'issuer'),
                'url' => config('services.firefly.url'),
                'namespace' => config('services.firefly.namespace'),
                'auth_mode' => config('services.firefly.auth_mode', 'bearer'),
                'api_key' => config('services.firefly.api_key'),
                'username' => config('services.firefly.username'),
                'password' => config('services.firefly.password'),
                'audit_topic' => config('services.firefly.audit_topic', 'audit'),
                'platform_identity' => config('services.firefly.platform_identity', 'platform'),
                'token_pool' => config('services.firefly.token_pool'),
                'token_name' => config('services.firefly.token_name'),
            ], 0);

            if ($fallback['enabled'] && ($fallback['base_url'] !== '' || $fallback['namespace'] !== '')) {
                $members[] = $fallback;
            }
        }

        return array_values($members);
    }

    protected function decodeMembers(mixed $members): array
    {
        if (is_array($members)) {
            return $members;
        }

        if (! is_string($members) || trim($members) === '') {
            return [];
        }

        $decoded = json_decode($members, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function normalizeMember(array $member, int $index): array
    {
        $label = (string) ($member['label'] ?? $member['name'] ?? 'FireFly Member ' . ($index + 1));
        $role = (string) ($member['role'] ?? ($index === 0 ? 'issuer' : 'validator'));
        $key = Str::slug((string) ($member['key'] ?? $label)) ?: 'member-' . ($index + 1);
        $authMode = strtolower((string) ($member['auth_mode'] ?? $member['auth'] ?? 'bearer'));

        return [
            'key' => $key,
            'label' => $label,
            'role' => $role,
            'description' => (string) ($member['description'] ?? ''),
            'base_url' => rtrim((string) ($member['url'] ?? $member['base_url'] ?? ''), '/'),
            'namespace' => (string) ($member['namespace'] ?? ''),
            'auth_mode' => $authMode,
            'api_key' => $member['api_key'] ?? $member['token'] ?? null,
            'username' => $member['username'] ?? null,
            'password' => $member['password'] ?? null,
            'audit_topic' => (string) ($member['audit_topic'] ?? 'audit'),
            'platform_identity' => (string) ($member['platform_identity'] ?? 'platform'),
            'token_ready' => (bool) (($member['token_pool'] ?? null) || ($member['token_name'] ?? null)),
            'enabled' => $this->normalizeBoolean($member['enabled'] ?? true),
        ];
    }

    protected function configuredMembers(): array
    {
        return array_values(array_filter($this->members, fn (array $member) => $this->isMemberConfigured($member)));
    }

    protected function isMemberConfigured(array $member): bool
    {
        return ($member['base_url'] ?? '') !== '' && ($member['namespace'] ?? '') !== '';
    }

    protected function determineQuorum(): int
    {
        $configuredMembers = count($this->configuredMembers());

        if ($configuredMembers === 0) {
            return 0;
        }

        $configuredQuorum = (int) config('services.firefly.consortium_quorum', 0);

        if ($configuredQuorum > 0) {
            return max(1, min($configuredQuorum, $configuredMembers));
        }

        return $configuredMembers > 1 ? min(2, $configuredMembers) : 1;
    }

    protected function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    protected function client(array $member): PendingRequest
    {
        $client = Http::baseUrl($member['base_url'])
            ->acceptJson()
            ->asJson();

        if ($member['auth_mode'] === 'basic' && ($member['username'] ?? null) && ($member['password'] ?? null)) {
            $client = $client->withBasicAuth((string) $member['username'], (string) $member['password']);
        } elseif ($member['auth_mode'] !== 'none' && ($member['api_key'] ?? null)) {
            $client = $client->withToken((string) $member['api_key']);
        }

        return $client;
    }

    protected function checkMember(array $member): array
    {
        $base = [
            'key' => $member['key'],
            'label' => $member['label'],
            'role' => $member['role'],
            'description' => $member['description'],
            'endpoint' => $member['base_url'],
            'namespace' => $member['namespace'],
            'auth_mode' => $member['auth_mode'],
            'platform_identity' => $member['platform_identity'],
            'audit_topic' => $member['audit_topic'],
            'configured' => $this->isMemberConfigured($member),
            'token_ready' => $member['token_ready'],
        ];

        if (! $base['configured']) {
            return array_merge($base, [
                'success' => false,
                'message' => 'Thiếu URL hoặc namespace.',
            ]);
        }

        $attempts = [
            ['/api/v1/status', 'status'],
            ['/status', 'status'],
            ["/api/v1/namespaces/{$member['namespace']}", 'namespace'],
        ];

        $lastError = null;

        foreach ($attempts as [$endpoint, $source]) {
            try {
                $response = $this->client($member)->get($endpoint);

                if ($response->successful()) {
                    return array_merge($base, [
                        'success' => true,
                        'message' => 'Kết nối thành công.',
                        'source' => $source,
                    ]);
                }

                $body = $response->json();
                $lastError = is_array($body)
                    ? ($body['error'] ?? $body['message'] ?? json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                    : $response->body();
            } catch (\Throwable $exception) {
                $lastError = $exception->getMessage();
            }
        }

        return array_merge($base, [
            'success' => false,
            'message' => $lastError ?: 'Không thể kết nối tới FireFly.',
        ]);
    }

    protected function formatResponse(Response $response, array $member, string $requestId): array
    {
        $body = $response->json();

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'data' => $body ?? $response->body(),
            'request_id' => $requestId,
            'member_key' => $member['key'],
            'member_label' => $member['label'],
            'member_role' => $member['role'],
            'endpoint' => $member['base_url'],
            'message' => $response->successful()
                ? 'Đã ghi nhận proof thành công.'
                : (is_array($body)
                    ? ($body['error'] ?? $body['message'] ?? json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                    : $response->body()),
            'message_id' => is_array($body) ? (data_get($body, 'header.id') ?? data_get($body, 'id')) : null,
            'tx_id' => is_array($body)
                ? (data_get($body, 'tx.id') ?? data_get($body, 'tx') ?? data_get($body, 'blockchain.transactionHash'))
                : null,
            'state' => is_array($body) ? (data_get($body, 'state') ?? data_get($body, 'status')) : null,
        ];
    }
}
