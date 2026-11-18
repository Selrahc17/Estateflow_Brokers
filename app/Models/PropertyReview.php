<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PropertyReview extends Model
{
    protected $fillable = ['property_id', 'user_id', 'rating', 'comment', 'hidden_at'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'hidden_at' => 'datetime',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Reviews everyone can see; an admin hides the rest. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }
}
