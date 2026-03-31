<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    public const DIRECT_METHOD = 'direct';
    public const DIRECT_TOPUP_EXPIRY_HOURS = 48;

    protected $fillable = [
        'wallet_id',
        'course_id',
        'type',
        'amount',
        'status',
        'reference',
        'expires_at',
        'expired_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeDirectTopups(Builder $query): Builder
    {
        return $query
            ->where('type', 'deposit')
            ->where('metadata->method', self::DIRECT_METHOD);
    }

    public function scopePendingDirectApproval(Builder $query): Builder
    {
        return $query
            ->directTopups()
            ->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->status === 'pending'
            && ! is_null($this->expires_at)
            && $this->expires_at->isPast();
    }

    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    public function isDirectTopup(): bool
    {
        return $this->isDeposit() && data_get($this->metadata, 'method') === self::DIRECT_METHOD;
    }

    public function complete(): bool
    {
        if (! $this->isPending() || ! $this->isDeposit()) {
            return false;
        }

        $this->status = 'completed';
        $this->expired_at = null;
        $this->save();

        $wallet = $this->wallet;
        $wallet->balance = $wallet->balance + $this->amount;
        $wallet->save();

        return true;
    }

    public function expire(?array $metadata = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'expired';
        $this->expired_at = $this->expired_at ?? now();

        if ($metadata) {
            $this->metadata = array_merge($this->metadata ?? [], $metadata);
        }

        $this->save();

        return true;
    }

    public function fail(?array $metadata = null): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        $this->status = 'failed';
        $this->expired_at = null;

        if ($metadata) {
            $this->metadata = array_merge($this->metadata ?? [], $metadata);
        }

        $this->save();

        return true;
    }

    public static function expireOverdueDirectTopups(): int
    {
        $transactions = static::query()
            ->pendingDirectApproval()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $expiredCount = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->expire([
                'expired_reason' => 'timeout',
                'expired_by_system' => true,
                'expired_at' => now()->toDateTimeString(),
            ])) {
                $expiredCount++;
            }
        }

        return $expiredCount;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ xử lý',
            'completed' => 'Hoàn thành',
            'failed' => 'Thất bại',
            'expired' => 'Đã hết hạn',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'expired' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getRequestedAtLabelAttribute(): ?string
    {
        return $this->created_at?->format('d/m/Y H:i');
    }

    public function getExpiresAtLabelAttribute(): ?string
    {
        return $this->expires_at?->format('d/m/Y H:i');
    }

    public function getExpiredAtLabelAttribute(): ?string
    {
        return $this->expired_at?->format('d/m/Y H:i');
    }

    public function getExpiryNoticeAttribute(): ?string
    {
        if (! $this->isDirectTopup()) {
            return null;
        }

        if ($this->status === 'expired') {
            return 'Đã hết hạn' . ($this->expired_at_label ? ' lúc ' . $this->expired_at_label : '');
        }

        if ($this->expires_at_label) {
            return 'Hết hạn lúc ' . $this->expires_at_label;
        }

        return null;
    }
}