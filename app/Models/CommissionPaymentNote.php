<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPaymentNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_payment_id',
        'sender_type',
        'sender_id',
        'message',
        'proof_path',
    ];

    public function payment()
    {
        return $this->belongsTo(CommissionPayment::class, 'commission_payment_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
