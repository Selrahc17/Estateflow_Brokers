<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_agreement_id',
        'reservation_id',
        'due_date',
        'amount_due',
        'agent_amount',
        'broker_amount',
        'amount_paid',
        'payment_status',
        'proof_path',
        'payment_message',
        'dispute_reason',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount_due' => 'decimal:2',
            'agent_amount' => 'decimal:2',
            'broker_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function agreement()
    {
        return $this->belongsTo(CommissionAgreement::class, 'commission_agreement_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function notes()
    {
        return $this->hasMany(CommissionPaymentNote::class);
    }
}
