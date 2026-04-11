<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;

class Payment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $payment): void {
            if (! filled($payment->class_id)) {
                return;
            }

            if (filled($payment->course_id) && ! $payment->isDirty('class_id')) {
                return;
            }

            $payment->course_id = CourseClass::query()
                ->whereKey($payment->class_id)
                ->value('course_id');
        });
    }

    protected $fillable = [
        'user_id',
        'course_id',
        'class_id',
        'amount',
        'base_amount',
        'discount_amount',
        'discount_code_id',
        'method',
        'status',
        'paid_at',
        'notes',
        'reference',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
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

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class, 'discount_code_id');
    }

    public function getCourseAttribute()
    {
        $this->loadMissing(['course', 'courseClass.course']);

        return $this->getRelation('course') ?: $this->courseClass?->course;
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
            'wallet' => 'VÃ­ ná»™i bá»™',
            'promotion' => 'Khuyáº¿n mÃ£i / miá»…n phÃ­',
            'vnpay' => 'VNPay',
            'bank_transfer' => 'Chuyá»ƒn khoáº£n',
            'cash' => 'Tiá»n máº·t',
            'counter' => 'Táº¡i quáº§y',
        ][$this->method] ?? ucfirst(str_replace('_', ' ', (string) $this->method));
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Chá» thanh toÃ¡n',
            'completed' => 'ÄÃ£ thanh toÃ¡n',
            'failed' => 'Tháº¥t báº¡i',
        ][$this->status] ?? ucfirst((string) $this->status);
    }

    public function getSavingsLabelAttribute(): ?string
    {
        if ((float) $this->discount_amount <= 0) {
            return null;
        }

        return number_format((float) $this->discount_amount, 0) . 'Ä‘';
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
