<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'broker_id', 'name', 'slug', 'description', 'address',
        'city', 'province', 'type', 'latitude', 'longitude', 'price', 'status',
        'bedrooms', 'bathrooms', 'floor_area', 'lot_area', 'frontage', 'stories', 'parking_slots',
        'featured_image', 'images', 'amenities', 'view_count',
    ];

    protected function casts(): array
    {
        return [
            'price'    => 'decimal:2',
            'images'   => 'array',
            'amenities' => 'array',
        ];
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    // A property can have many favorites
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // A property can have many inquiries
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    // A property can have many site visits
    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }
}