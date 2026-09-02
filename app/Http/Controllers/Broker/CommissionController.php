<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\CommissionAgreement;
use App\Models\CommissionPayment;
use App\Models\CommissionPaymentNote;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        $agreementIds = CommissionAgreement::where('broker_id', auth()->id())
            ->selectRaw('MAX(id) as id')
            ->groupBy('property_id')
            ->pluck('id');

        $agreements = CommissionAgreement::whereIn('id', $agreementIds)
            ->with(['agent', 'property', 'payments'])
            ->latest()
            ->paginate(10);

        $totalExpected = $agreements->getCollection()->sum(fn ($agreement) => $agreement->payments->sum('amount_due'));
        $totalPaid = $agreements->getCollection()->sum(fn ($agreement) => $agreement->payments->where('payment_status', 'paid')->sum('amount_paid'));
        $totalDisputed = $agreements->getCollection()->sum(fn ($agreement) => $agreement->payments->where('payment_status', 'disputed')->sum('amount_due'));

        return view('pages.broker.commission.index', compact('agreements', 'totalExpected', 'totalPaid', 'totalDisputed'));
    }

    public function create(): View
    {
        $brokerId = auth()->id();
        $agentIds = User::where('role', 'agent')
            ->where('broker_id', $brokerId)
            ->pluck('id');

        $properties = Property::where(function ($query) use ($brokerId, $agentIds) {
            $query->where('broker_id', $brokerId)
                ->orWhereIn('broker_id', $agentIds);
        })->get();

        $agents = User::where('role', 'agent')
            ->where('broker_id', $brokerId)
            ->get();

        return view('pages.broker.commission.create', compact('agents', 'properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $brokerId = auth()->id();
        $agentIds = User::where('role', 'agent')
            ->where('broker_id', $brokerId)
            ->pluck('id');

        $data = $request->validate([
            'agent_id' => ['required', Rule::exists('users', 'id')->where(fn($query) => $query->where('role', 'agent')->where('broker_id', $brokerId))],
            'property_id' => ['nullable', Rule::exists('properties', 'id')->where(function ($query) use ($brokerId, $agentIds) {
                $query->where('broker_id', $brokerId)
                    ->orWhereIn('broker_id', $agentIds);
            })],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'agent_share' => ['required', 'numeric', 'min:0', 'max:100'],
            'broker_share' => ['required', 'numeric', 'min:0', 'max:100'],
            'payment_schedule' => ['required', 'in:monthly,every_15th,quarterly,annual,custom'],
            'payment_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $data['broker_id'] = auth()->id();
        $data['status'] = 'active';

        $agreement = CommissionAgreement::create($data);
        $agreement->generateScheduledPayments();

        return redirect()->route('broker.commissions.index')->with('success', 'Commission agreement created successfully.');
    }

    public function show(CommissionAgreement $agreement): View
    {
        $this->ensureOwnership($agreement);
        $agreement->load(['agent', 'broker', 'property', 'payments.notes']);
        $currentPayment = $this->currentPayment($agreement);

        return view('pages.broker.commission.show', compact('agreement', 'currentPayment'))->with('paymentMode', false);
    }

    public function pay(CommissionAgreement $agreement): View
    {
        $this->ensureOwnership($agreement);
        $agreement->load(['agent', 'broker', 'property', 'payments.notes']);
        $currentPayment = $this->currentPayment($agreement);

        return view('pages.broker.commission.pay', compact('agreement', 'currentPayment'));
    }

    public function updatePayment(Request $request, CommissionAgreement $agreement): RedirectResponse
    {
        $this->ensureOwnership($agreement);

        $data = $request->validate([
            'payment_id' => ['required', 'integer', Rule::exists('commission_payments', 'id')->where(fn ($query) => $query->where('commission_agreement_id', $agreement->id))],
            'amount_paid' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,gcash,maya,check,other'],
            'payment_message' => ['nullable', 'string', 'max:1000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $payment = $agreement->payments()->findOrFail($data['payment_id']);

        if ($payment->payment_status === 'paid') {
            return back()->withErrors(['payment' => 'This payment has already been recorded.']);
        }

        if (!$payment->due_date || !Carbon::parse($payment->due_date)->isSameMonth(now())) {
            return back()->withErrors(['payment' => 'This payment can only be submitted during its due month.']);
        }

        if ((float) $data['amount_paid'] !== (float) $payment->amount_due) {
            return back()->withErrors(['amount_paid' => 'The amount paid must match the monthly amount due.'])->withInput();
        }

        $path = $request->file('proof')->store('commission-proof', 'public');

        $payment->update([
            'payment_status' => 'paid',
            'amount_paid' => $data['amount_paid'],
            'payment_method' => $data['payment_method'],
            'proof_path' => $path,
            'payment_message' => $data['payment_message'] ?? $payment->payment_message,
            'paid_at' => now(),
        ]);

        if (!empty($data['payment_message'])) {
            CommissionPaymentNote::create([
                'commission_payment_id' => $payment->id,
                'sender_type' => 'broker',
                'sender_id' => auth()->id(),
                'message' => $data['payment_message'],
                'proof_path' => $payment->proof_path,
            ]);
        }

        return redirect()->route('broker.commissions.show', $agreement)->with('success', 'Commission payment status updated successfully.');
    }

    private function ensureOwnership(CommissionAgreement $agreement): void
    {
        abort_unless((int) $agreement->broker_id === (int) auth()->id(), 403);
    }

    private function currentPayment(CommissionAgreement $agreement): ?CommissionPayment
    {
        return $agreement->payments
            ->where('payment_status', '!=', 'paid')
            ->filter(fn ($payment) => $payment->due_date && Carbon::parse($payment->due_date)->isSameMonth(now()))
            ->first();
    }
}
