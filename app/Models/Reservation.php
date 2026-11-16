<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * "Requested" comes from a buyer and does not hold the lot until the agent
     * confirms it. Pending and confirmed reservations hold the lot.
     */
    public const STATUSES = [
        'requested' => 'Requested by buyer',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const HOLDING = ['pending', 'confirmed'];

    public function holdsLot(): bool
    {
        return in_array($this->status, self::HOLDING, true);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    protected $fillable = [
        'client_id', 'agent_id', 'lot_id', 'broker_id', 'reservation_code',
        'status', 'total_price', 'reservation_fee', 'down_payment', 'payment_schedule',
        'payment_terms_months', 'notes', 'reserved_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'total_price'        => 'decimal:2',
            'reservation_fee'    => 'decimal:2',
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

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}