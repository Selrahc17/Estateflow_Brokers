<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id', 'receiver_id', 'message', 'attachment', 'is_read', 'delivered_at', 'seen_at', 'sender_type',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'delivered_at' => 'datetime',
            'seen_at' => 'datetime',
        ];
    }

    /**
     * Older messages stored a full URL; newer photos are private and need a sign-in.
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment) {
            return null;
        }

        return str_starts_with($this->attachment, 'http') ? $this->attachment : route('files.chat-photo', $this);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Messages a user sent to the AI have no receiver; AI replies are addressed to the user.
     * Person-to-person messages always have both people set, so they never show up here.
     */
    public function scopeAiConversationFor($query, int $userId)
    {
        return $query->where(fn ($conversation) => $conversation
            ->where(fn ($mine) => $mine->where('sender_type', 'user')->where('sender_id', $userId)->whereNull('receiver_id'))
            ->orWhere(fn ($replies) => $replies->where('sender_type', 'ai')->where('receiver_id', $userId)));
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    public static function aiHistoryFor(int $userId, int $limit = 10): array
    {
        return static::aiConversationFor($userId)->latest('id')->limit($limit)->get(['sender_type', 'message'])
            ->reverse()
            ->map(fn (self $message) => ['role' => $message->sender_type === 'ai' ? 'assistant' : 'user', 'content' => (string) $message->message])
            ->values()
            ->all();
    }

    public function getMessageHtmlAttribute(): string
    {
        return \App\Support\ChatFormatter::toHtml($this->message);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}