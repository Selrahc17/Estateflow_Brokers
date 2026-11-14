<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A sold listing or lot, for the broker's commission table.
 *
 * Commission amounts are private to the broker: agents see their own sales,
 * and the status only when the broker ticks "Show status to agent".
 */
class Sale extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'transferred' => 'Transferred',
        'received' => 'Received',
    ];

    protected $fillable = [
        'broker_id', 'agent_id', 'property_id', 'lot_id', 'sold_at', 'selling_price',
        'agent_commission', 'broker_commission', 'status', 'show_status_to_agent', 'source', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'date',
            'selling_price' => 'decimal:2',
            'agent_commission' => 'decimal:2',
            'broker_commission' => 'decimal:2',
            'show_status_to_agent' => 'boolean',
        ];
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /** Includes removed listings, so old sales still show what was sold. */
    public function property()
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class)->withTrashed();
    }

    /** Sales by this broker's agents, plus ones the broker entered. */
    public function scopeForBroker(Builder $query, User $broker): Builder
    {
        return $query->where('broker_id', $broker->id);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** "Vista Homes" or "Vista Homes · Blk 25 Lot 13". */
    public function getItemNameAttribute(): string
    {
        $name = $this->property?->name ?? 'Removed listing';

        return $this->lot ? "{$name} · {$this->lot->lot_number}" : $name;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $photo = $this->property?->gallery_images[0] ?? null;

        if (! $photo || preg_match('#^(https?:|data:)#', $photo)) {
            return $photo;
        }

        return asset('storage/' . ltrim($photo, '/'));
    }

    /**
     * Records a sale when an agent marks a listing or one of its lots as Sold.
     * Does nothing if this listing or lot already has a sale.
     */
    public static function recordFromListing(Property $property, ?Lot $lot = null): ?self
    {
        $existing = static::where('property_id', $property->id)
            ->when($lot, fn ($query) => $query->where('lot_id', $lot->id), fn ($query) => $query->whereNull('lot_id'))
            ->exists();

        if ($existing) {
            return null;
        }

        $agent = User::find($property->broker_id);
        $price = $lot?->price ?? $property->price ?? $property->lowest_model_price;

        return static::create([
            'broker_id' => $agent?->role === 'agent' ? $agent->broker_id : $agent?->id,
            'agent_id' => $agent?->id,
            'property_id' => $property->id,
            'lot_id' => $lot?->id,
            'sold_at' => now()->toDateString(),
            'selling_price' => (float) $price > 0 ? $price : null,
            'status' => 'pending',
            'source' => 'auto',
        ]);
    }

    /**
     * When Sold is undone, removes the automatic record if the broker has not
     * worked on it yet. A record the broker changed is kept.
     */
    public static function undoFromListing(Property $property, ?Lot $lot = null): void
    {
        static::where('property_id', $property->id)
            ->when($lot, fn ($query) => $query->where('lot_id', $lot->id), fn ($query) => $query->whereNull('lot_id'))
            ->where('source', 'auto')
            ->where('status', 'pending')
            ->whereNull('agent_commission')
            ->whereNull('broker_commission')
            ->delete();
    }
}
