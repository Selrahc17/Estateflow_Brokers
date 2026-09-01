<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'broker_id',
        'agent_id',
        'property_id',
        'commission_rate',
        'broker_share',
        'agent_share',
        'payment_schedule',
        'payment_day',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'broker_share' => 'decimal:2',
            'agent_share' => 'decimal:2',
            'payment_day' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function payments()
    {
        return $this->hasMany(CommissionPayment::class);
    }
}
