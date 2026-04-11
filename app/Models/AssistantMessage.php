<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;

class AssistantMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistant_conversation_id',
        'role',
        'message',
        'recommended_courses',
        'meta',
    ];

    protected $casts = [
        'recommended_courses' => 'array',
        'meta' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(AssistantConversation::class, 'assistant_conversation_id');
    }
}
