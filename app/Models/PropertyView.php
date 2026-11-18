<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyView extends Model
{
    protected $fillable = ['user_id', 'property_id', 'visits', 'last_viewed_at'];

    protected function casts(): array
    {
        return [
            'visits' => 'integer',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /** Counts one more visit by this client, creating the row on the first one. */
    public static function record(User $user, Property $property): void
    {
        $view = static::firstOrNew(['user_id' => $user->id, 'property_id' => $property->id]);
        $view->visits = $view->exists ? $view->visits + 1 : 1;
        $view->last_viewed_at = now();
        $view->save();
    }
}
