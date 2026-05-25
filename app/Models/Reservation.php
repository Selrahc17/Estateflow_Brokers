<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'lot_id', 'broker_id', 'reservation_code',
        'status', 'total_price', 'down_payment', 'payment_schedule',
        'payment_terms_months', 'notes', 'reserved_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'total_price'        => 'decimal:2',
            'down_payment'       => 'decimal:2',
            'payment_terms_months' => 'integer',
            'reserved_at'        => 'datetime',
            'expires_at'         => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getMonthlyPaymentAttribute(): ?float
    {
        if ($this->total_price && $this->payment_terms_months > 0) {
            $balance = $this->total_price - ($this->down_payment ?? 0);
            return round($balance / $this->payment_terms_months, 2);
        }
        return null;
    }
}