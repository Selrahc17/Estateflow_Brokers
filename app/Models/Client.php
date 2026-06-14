<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Authenticatable
{
    use Notifiable, SoftDeletes;

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
        'remember_token',
        'email_verification_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }
}
