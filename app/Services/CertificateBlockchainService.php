<?php

namespace App\Services;

use App\Models\CourseCertificate;

class CertificateBlockchainService
{
    public function __construct(
        protected BlockchainAuditService $blockchainAudit,
        protected FireflyConsortiumService $consortium,
    ) {
    }

    public function ensureAnchored(CourseCertificate $certificate): CourseCertificate
    {
        $certificate->loadMissing([
            'course:id,title,learning_type',
            'user:id,fullname,username,email,role',
            'enrollment:id,class_id,completed_at',
            'enrollment.courseClass:id,name,start_date',
        ]);

        $meta = $certificate->meta ?? [];
        $payload = $this->buildPayload($certificate);
        $verificationHash = $this->buildVerificationHash($payload);

        $meta['verification_hash'] = $verificationHash;
        $meta['verification_url'] = route('certificates.verify', ['code' => $certificate->certificate_no]);

        if (! data_get($meta, 'blockchain_audit.success') && $this->consortium->isConfigured()) {
            $audit = $this->blockchainAudit->record(
                'certificate.issued',
                [
                    'certificate_no' => $certificate->certificate_no,
                    'verification_hash' => $verificationHash,
                    'certificate' => $payload,
                ],
                [
                    'reference' => $certificate->certificate_no,
                    'user_id' => $certificate->user_id,
                    'username' => $certificate->user->username ?? null,
                    'role' => $certificate->user->role ?? null,
                ]
            );

            $meta['blockchain_audit'] = $audit;
        } elseif (! isset($meta['blockchain_audit']) && ! $this->consortium->isConfigured()) {
            $meta['blockchain_audit'] = [
                'success' => false,
                'message' => 'FireFly chưa được cấu hình.',
            ];
        }

        $certificate->meta = $meta;
        $certificate->save();

        return $certificate->fresh([
            'course:id,title,learning_type',
            'user:id,fullname,username,email,role',
            'enrollment:id,class_id,completed_at',
            'enrollment.courseClass:id,name,start_date',
        ]);
    }

    public function verificationSnapshot(CourseCertificate $certificate): array
    {
        $audit = data_get($certificate->meta, 'blockchain_audit', []);
        $verificationUrl = data_get($certificate->meta, 'verification_url')
            ?: route('certificates.verify', ['code' => $certificate->certificate_no]);
        $memberResults = collect(data_get($audit, 'member_results', []))
            ->map(function (array $result, string $key) {
                return [
                    'key' => $result['member_key'] ?? $key,
                    'label' => $result['member_label'] ?? ucwords(str_replace('-', ' ', $key)),
                    'role' => $result['member_role'] ?? 'validator',
                    'success' => (bool) ($result['success'] ?? false),
                    'message_id' => $result['message_id'] ?? data_get($result, 'data.header.id') ?? data_get($result, 'data.id'),
                    'tx_id' => $result['tx_id'] ?? data_get($result, 'data.tx.id') ?? data_get($result, 'data.tx') ?? data_get($result, 'data.blockchain.transactionHash'),
                    'state' => $result['state'] ?? data_get($result, 'data.state') ?? ($result['message'] ?? null),
                    'endpoint' => $result['endpoint'] ?? null,
                ];
            })
            ->values()
            ->all();
        $successCount = (int) data_get($audit, 'success_count', (data_get($audit, 'success') ? 1 : 0));
        $requiredQuorum = max((int) data_get($audit, 'required_quorum', ($memberResults !== [] ? 1 : 0)), 1);
        $membersTotal = (int) data_get($audit, 'members_total', count($memberResults));

        return [
            'hash' => data_get($certificate->meta, 'verification_hash'),
            'verification_url' => $verificationUrl,
            'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($verificationUrl),
            'is_blockchain_verified' => (bool) data_get($audit, 'success', false),
            'audit' => $audit,
            'firefly_message_id' => data_get($audit, 'message_id')
                ?? data_get($audit, 'data.header.id')
                ?? data_get($audit, 'data.id'),
            'firefly_tx_id' => data_get($audit, 'tx_id')
                ?? data_get($audit, 'data.tx.id')
                ?? data_get($audit, 'data.tx')
                ?? data_get($audit, 'data.blockchain.transactionHash'),
            'firefly_state' => data_get($audit, 'state')
                ?? data_get($audit, 'data.state')
                ?? data_get($audit, 'status'),
            'consortium_success_count' => $successCount,
            'consortium_required_quorum' => $requiredQuorum,
            'consortium_members_total' => $membersTotal,
            'consortium_member_results' => $memberResults,
        ];
    }

    public function buildPayload(CourseCertificate $certificate): array
    {
        return [
            'certificate_no' => $certificate->certificate_no,
            'issued_at' => optional($certificate->issued_at)->toIso8601String(),
            'student' => [
                'id' => $certificate->user_id,
                'name' => $certificate->user->fullname ?: $certificate->user->username,
                'email' => $certificate->user->email,
            ],
            'course' => [
                'id' => $certificate->course_id,
                'title' => $certificate->course->title ?? null,
                'learning_type' => $certificate->course->learning_type ?? null,
            ],
            'class' => [
                'name' => $certificate->enrollment?->courseClass?->name,
                'completed_at' => optional($certificate->enrollment?->completed_at)->toIso8601String(),
            ],
        ];
    }

    protected function buildVerificationHash(array $payload): string
    {
        return hash(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
