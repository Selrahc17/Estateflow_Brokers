<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'broker_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'date_of_birth',
        'civil_status',
        'address',
        'profile_photo',
        'status',
        'email_verified',
        'email_verification_token',
    ];

    protected $hidden = [
        'password',
        'email_verification_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'email_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // A client belongs to a user account
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // A client belongs to a broker
    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }
}