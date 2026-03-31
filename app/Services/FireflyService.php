<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FireflyService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected ?string $namespace;
    protected ?string $tokenPool;
    protected ?string $legacyTokenName;
    protected ?string $platformIdentity;
    protected ?string $signer;
    protected ?string $auditTopic;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.firefly.url');
        $this->apiKey = config('services.firefly.api_key');
        $this->namespace = config('services.firefly.namespace');
        $this->tokenPool = config('services.firefly.token_pool');
        $this->legacyTokenName = config('services.firefly.token_name');
        $this->platformIdentity = config('services.firefly.platform_identity');
        $this->signer = config('services.firefly.signer');
        $this->auditTopic = config('services.firefly.audit_topic');
    }

    protected function client()
    {
        $client = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->asJson();

        if ($this->apiKey) {
            $client = $client->withToken($this->apiKey);
        }

        return $client;
    }

    public function isConfigured(): bool
    {
        return (bool) ($this->baseUrl && $this->namespace);
    }

    public function canManageTokens(): bool
    {
        return $this->isConfigured() && (bool) ($this->tokenPool || $this->legacyTokenName);
    }

    public function getPlatformIdentity(): string
    {
        return $this->platformIdentity ?: 'platform';
    }

    public function mint(string $toIdentity, float $amount, array $context = []): array
    {
        if (! $this->canManageTokens()) {
            return ['success' => false, 'message' => 'FireFly token integration not configured'];
        }

        $requestId = (string) ($context['request_id'] ?? $context['reference'] ?? Str::uuid());
        $payload = $this->tokenPayload([
            'to' => $toIdentity,
            'amount' => (string) $amount,
            'data' => $this->normalizeDataField($context['data'] ?? null),
            'idempotencyKey' => $requestId,
        ]);

        $response = $this->client()->post("/api/v1/namespaces/{$this->namespace}/tokens/mint", $payload);

        if ($response->status() === 404 && $this->legacyTokenName) {
            $response = $this->client()->post("/api/v1/namespaces/{$this->namespace}/tokens/{$this->legacyTokenName}/mint", [
                'to' => $toIdentity,
                'amount' => (string) $amount,
            ]);
        }

        return $this->formatResponse($response, $requestId);
    }

    public function transfer(string $fromIdentity, string $toIdentity, float $amount, array $context = []): array
    {
        if (! $this->canManageTokens()) {
            return ['success' => false, 'message' => 'FireFly token integration not configured'];
        }

        $requestId = (string) ($context['request_id'] ?? $context['reference'] ?? Str::uuid());
        $payload = $this->tokenPayload([
            'from' => $fromIdentity,
            'to' => $toIdentity,
            'amount' => (string) $amount,
            'data' => $this->normalizeDataField($context['data'] ?? null),
            'idempotencyKey' => $requestId,
        ]);

        $response = $this->client()->post("/api/v1/namespaces/{$this->namespace}/tokens/transfer", $payload);

        if ($response->status() === 404 && $this->legacyTokenName) {
            $response = $this->client()->post("/api/v1/namespaces/{$this->namespace}/tokens/{$this->legacyTokenName}/transfer", [
                'from' => $fromIdentity,
                'to' => $toIdentity,
                'amount' => (string) $amount,
            ]);
        }

        return $this->formatResponse($response, $requestId);
    }

    public function getBalance(string $identity): array
    {
        if (! $this->canManageTokens()) {
            return ['success' => false, 'message' => 'FireFly token integration not configured'];
        }

        $response = $this->client()->get("/api/v1/namespaces/{$this->namespace}/tokens/balances", $this->cleanArray([
            'identity' => $identity,
            'pool' => $this->tokenPool,
        ]));

        if ($response->status() === 404 && $this->legacyTokenName) {
            $response = $this->client()->get("/api/v1/namespaces/{$this->namespace}/tokens/{$this->legacyTokenName}/balances", [
                'identity' => $identity,
            ]);
        }

        return $this->formatResponse($response);
    }

    public function broadcastAuditEvent(string $eventType, array $payload, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'FireFly not configured'];
        }

        $requestId = (string) ($context['request_id'] ?? Str::uuid());
        $message = [
            'event' => $eventType,
            'topic' => $this->auditTopic ?: 'audit',
            'payload' => $payload,
        ];

        $response = $this->client()->post("/api/v1/namespaces/{$this->namespace}/messages/broadcast", [
            'idempotencyKey' => $requestId,
            'data' => [[
                'value' => json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]],
        ]);

        return $this->formatResponse($response, $requestId);
    }

    protected function tokenPayload(array $payload): array
    {
        $payload = $this->cleanArray($payload);

        if (! isset($payload['pool']) && $this->tokenPool) {
            $payload['pool'] = $this->tokenPool;
        }

        if (! isset($payload['key']) && $this->signer) {
            $payload['key'] = $this->signer;
        }

        return $payload;
    }

    protected function normalizeDataField(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function cleanArray(array $data): array
    {
        return array_filter($data, static function ($value) {
            if (is_array($value)) {
                return $value !== [];
            }

            return $value !== null && $value !== '';
        });
    }

    protected function formatResponse(Response $response, ?string $requestId = null): array
    {
        $body = $response->json();

        $formatted = [
            'success' => $response->successful(),
            'status' => $response->status(),
            'data' => $body ?? $response->body(),
        ];

        if (! $response->successful()) {
            $formatted['message'] = is_array($body)
                ? ($body['error'] ?? $body['message'] ?? json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                : $response->body();
        }

        if ($requestId) {
            $formatted['request_id'] = $requestId;
        }

        if (is_array($body)) {
            $formatted['message_id'] = data_get($body, 'header.id') ?? data_get($body, 'id');
            $formatted['tx_id'] = data_get($body, 'tx.id')
                ?? data_get($body, 'tx')
                ?? data_get($body, 'blockchain.id')
                ?? data_get($body, 'blockchain.transactionHash');
            $formatted['state'] = data_get($body, 'state') ?? data_get($body, 'status');
        }

        return $formatted;
    }
}