<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'property_id', 'broker_id', 'type', 'lot_id', 'contact_name', 'message', 'preferred_viewing_at', 'status', 'phone', 'email', 'facebook'])]
class Inquiry extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'site_visit_scheduled' => 'Site Visit Scheduled',
        'negotiating' => 'Negotiating',
        'closed' => 'Closed',
        'lost' => 'Lost',
    ];

    protected $fillable = [
        'user_id',
        'property_id',
        'broker_id',
        'type',
        'lot_id',
        'contact_name',
        'message',
        'preferred_viewing_at',
        'status',
        'phone',
        'email',
        'facebook',
    ];
    use HasFactory;

    protected function casts(): array
    {
        return [
            'preferred_viewing_at' => 'datetime',
        ];
    }

    /** A request to view the property, with a preferred date and time. */
    public function isViewingRequest(): bool
    {
        return $this->type === 'viewing';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getContactLabelAttribute(): string
    {
        return $this->contact_name ?: ($this->user?->name ?? 'Guest');
    }

    /**
     * Only real Facebook addresses become links, since buyers type this field themselves.
     */
    public function getFacebookUrlAttribute(): ?string
    {
        return preg_match('~^https?://(www\.|m\.|web\.)?(facebook\.com|fb\.com|fb\.me)/~i', (string) $this->facebook) ? $this->facebook : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function siteVisit()
    {
        return $this->hasOne(SiteVisit::class);
    }
}
