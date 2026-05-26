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
        'featured_image', 'images', 'amenities',
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
}