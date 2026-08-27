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

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}