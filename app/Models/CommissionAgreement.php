<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CommissionAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'broker_id',
        'agent_id',
        'property_id',
        'reservation_id',
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

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function payments()
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function generateScheduledPayments(): Collection
    {
        $baseAmount = (float) ($this->reservation?->total_price ?? $this->property?->price ?? 0);
        $totalCommission = $baseAmount * ((float) $this->commission_rate / 100);
        $agentAmount = $totalCommission * ((float) $this->agent_share / 100);
        $brokerAmount = $totalCommission * ((float) $this->broker_share / 100);

        $scheduleType = $this->payment_schedule ?: 'monthly';
        $startDate = $this->start_date ? Carbon::parse($this->start_date) : Carbon::today();
        $endDate = $this->end_date ? Carbon::parse($this->end_date) : $startDate->copy()->addMonth();

        $dates = collect();
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dueDate = match ($scheduleType) {
                'every_15th' => $current->copy()->day($this->payment_day ?: 15),
                'quarterly' => $current->copy()->day($this->payment_day ?: 15)->month($current->month + 2),
                'annual' => $current->copy()->day($this->payment_day ?: 15)->month($this->payment_day ? 12 : 12),
                default => $current->copy()->day($this->payment_day ?: 15),
            };

            if ($dueDate->lt($startDate) || $dueDate->gt($endDate)) {
                $current = $current->addMonth();
                continue;
            }

            $dates->push($dueDate->copy());
            $current = match ($scheduleType) {
                'quarterly' => $current->copy()->addMonths(3),
                'annual' => $current->copy()->addYear(),
                'every_15th' => $current->copy()->addMonth(),
                default => $current->copy()->addMonth(),
            };
        }

        $dates = $dates->unique(fn ($date) => $date->format('Y-m-d'))->values();

        $generated = collect();

        foreach ($dates as $index => $dueDate) {
            $amountDue = $totalCommission;
            $amountPerPeriod = $dates->count() > 0 ? round($amountDue / $dates->count(), 2) : $amountDue;

            if ($index === $dates->count() - 1) {
                $amountPerPeriod = round($amountDue - ($amountPerPeriod * ($dates->count() - 1)), 2);
            }

            $payment = $this->payments()->updateOrCreate(
                ['due_date' => $dueDate->toDateString()],
                [
                    'reservation_id' => null,
                    'due_date' => $dueDate->toDateString(),
                    'amount_due' => $amountPerPeriod,
                    'agent_amount' => round($amountPerPeriod * ((float) $this->agent_share / 100), 2),
                    'broker_amount' => round($amountPerPeriod * ((float) $this->broker_share / 100), 2),
                    'amount_paid' => 0,
                    'payment_status' => 'pending',
                ]
            );

            $generated->push($payment);
        }

        return $generated;
    }
}
