<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'broker_id', 'is_active', 'is_approved', 'phone', 'avatar', 'license_number',
        'prc_id_expires_at', 'dhsud_registration_number', 'dhsud_registration_expires_at', 'tin', 'privacy_consented_at',
        'invitation_token', 'invitation_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'tin', 'invitation_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'prc_id_expires_at' => 'date',
            'dhsud_registration_expires_at' => 'date',
            'privacy_consented_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
        ];
    }

    // Role checks
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // An agent who has been invited but has not finished setting up their account
    public function isInvited(): bool
    {
        return $this->role === 'agent' && ! $this->is_approved;
    }

    public function broker()
    {
        return $this->belongsTo(self::class, 'broker_id');
    }

    public function agents()
    {
        return $this->hasMany(self::class, 'broker_id')->where('role', 'agent');
    }

    // A broker has many clients
    public function clients()
    {
        return $this->hasMany(Client::class, 'broker_id');
    }

    // A user can have one client profile
    public function clientProfile()
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    // A client can have many favorite properties
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // A client can have many inquiries
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    // A broker receives many inquiries
    public function receivedInquiries()
    {
        return $this->hasMany(Inquiry::class, 'broker_id');
    }

    // A broker has many properties
    public function properties()
    {
        return $this->hasMany(Property::class, 'broker_id');
    }

    // A broker has many site visits
    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class, 'broker_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'broker_id');
    }

    public function agentDocuments()
    {
        return $this->hasMany(Document::class, 'agent_id');
    }

    // A broker's own licensing documents, excluding files about their agents
    public function complianceDocuments()
    {
        return $this->hasMany(Document::class, 'broker_id')
            ->whereNull('agent_id')
            ->whereIn('type', array_keys(Document::BROKER_COMPLIANCE_TYPES));
    }
}