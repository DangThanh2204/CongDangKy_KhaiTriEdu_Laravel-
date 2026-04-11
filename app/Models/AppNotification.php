<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class AppNotification extends MongoModel
{
    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return false;
        }

        return (bool) $this->forceFill(['read_at' => now()])->save();
    }
}
