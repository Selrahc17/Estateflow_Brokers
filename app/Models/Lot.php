<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'sold' => 'Sold',
    ];

    protected $fillable = [
        'property_id', 'lot_number', 'price', 'square_meters',
        'status', 'description', 'title',
    ];

    /** "2" for "Blk 2 Lot 45", or null when the lot number has no block. */
    public function getBlockAttribute(): ?string
    {
        return preg_match('/^\s*(?:blk|block)\.?\s*([\w-]+)/i', (string) $this->lot_number, $match) ? $match[1] : null;
    }

    /** "45" for "Blk 2 Lot 45"; the whole lot number when it has no "Lot" part. */
    public function getShortNumberAttribute(): string
    {
        return preg_match('/lot\.?\s*([\w-]+)\s*$/i', (string) $this->lot_number, $match) ? $match[1] : (string) $this->lot_number;
    }

    /** "Lot 45" for a bare number, but "Blk 2 Lot 45" stays as it is. */
    public function getLabelAttribute(): string
    {
        return preg_match('/\blot\b/i', (string) $this->lot_number) ? (string) $this->lot_number : "Lot {$this->lot_number}";
    }

    public function getPricePerSqmAttribute(): ?float
    {
        return (float) $this->square_meters > 0 && (float) $this->price > 0 ? round((float) $this->price / (float) $this->square_meters, 2) : null;
    }

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