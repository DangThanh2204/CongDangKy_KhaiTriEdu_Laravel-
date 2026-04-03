<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'amount',
        'method',
        'status',
        'paid_at',
        'notes',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function getCourseAttribute()
    {
        $this->loadMissing('courseClass.course');

        return $this->courseClass?->course;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isVnpay(): bool
    {
        return $this->method === 'vnpay';
    }

    public function getMethodLabelAttribute(): string
    {
        return [
            'wallet' => 'Ví nội bộ',
            'vnpay' => 'VNPay',
            'bank_transfer' => 'Chuyển khoản',
            'cash' => 'Tiền mặt',
            'counter' => 'Tại quầy',
        ][$this->method] ?? ucfirst(str_replace('_', ' ', (string) $this->method));
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Chờ thanh toán',
            'completed' => 'Đã thanh toán',
            'failed' => 'Thất bại',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    public function markCompleted(?string $note = null): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $payload = [
            'status' => 'completed',
            'paid_at' => now(),
        ];

        if ($note !== null) {
            $payload['notes'] = trim($note);
        }

        $this->update($payload);

        return true;
    }

    public function markFailed(?string $reason = null): bool
    {
        if ($this->isFailed()) {
            return false;
        }

        $payload = [
            'status' => 'failed',
        ];

        if ($reason !== null && trim($reason) !== '') {
            $payload['notes'] = trim($reason);
        }

        $this->update($payload);

        return true;
    }
}
