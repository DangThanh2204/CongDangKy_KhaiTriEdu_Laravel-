<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'started_at',
        'last_message_at',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_message_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AssistantMessage::class)->orderBy('id');
    }
}
