<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'property_id', 'broker_id', 'lot_id', 'message', 'status', 'phone', 'email'])]
class Inquiry extends Model
{
    use HasFactory;

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
