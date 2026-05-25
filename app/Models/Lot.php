<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id', 'lot_number', 'price', 'square_meters',
        'status', 'description', 'title',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'square_meters' => 'decimal:2',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}