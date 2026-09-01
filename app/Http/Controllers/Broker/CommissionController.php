<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\CommissionAgreement;
use App\Models\CommissionPayment;
use App\Models\CommissionPaymentNote;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        $agreements = CommissionAgreement::where('broker_id', auth()->id())
            ->with(['agent', 'property'])
            ->latest()
            ->paginate(10);

        return view('pages.broker.commission.index', compact('agreements'));
    }

    public function create(): View
    {
        $agents = User::where('role', 'agent')
            ->where('broker_id', auth()->id())
            ->get();

        $properties = Property::where('broker_id', auth()->id())->get();

        return view('pages.broker.commission.create', compact('agents', 'properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'agent_id' => ['required', Rule::exists('users', 'id')->where(fn($query) => $query->where('role', 'agent')->where('broker_id', auth()->id()))],
            'property_id' => ['nullable', Rule::exists('properties', 'id')->where('broker_id', auth()->id())],
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

        $propertyPrice = optional($agreement->property)->price ?? 0;
        $agreement->payments()->create([
            'reservation_id' => null,
            'due_date' => $agreement->start_date ?? now()->toDateString(),
            'amount_due' => $propertyPrice,
            'agent_amount' => $propertyPrice * ($agreement->agent_share / 100),
            'broker_amount' => $propertyPrice * ($agreement->broker_share / 100),
            'amount_paid' => 0,
            'payment_status' => 'pending',
        ]);

        return redirect()->route('broker.commissions.index')->with('success', 'Commission agreement created successfully.');
    }

    public function show(CommissionAgreement $agreement): View
    {
        $this->ensureOwnership($agreement);
        $agreement->load(['agent', 'broker', 'property', 'payments.notes']);

        return view('pages.broker.commission.show', compact('agreement'));
    }

    public function updatePayment(Request $request, CommissionAgreement $agreement): RedirectResponse
    {
        $this->ensureOwnership($agreement);

        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,scheduled,sent,confirmed,disputed,paid'],
            'payment_message' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $payment = $agreement->payments()->latest()->first();

        if (!$payment) {
            $payment = $agreement->payments()->create([
                'due_date' => now()->toDateString(),
                'amount_due' => 0,
                'agent_amount' => 0,
                'broker_amount' => 0,
                'amount_paid' => 0,
                'payment_status' => 'pending',
            ]);
        }

        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('commission-proof', 'public');
            $payment->update(['proof_path' => $path]);
        }

        $payment->update([
            'payment_status' => $data['payment_status'],
            'payment_message' => $data['payment_message'] ?? $payment->payment_message,
            'paid_at' => $data['payment_status'] === 'paid' ? now() : $payment->paid_at,
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
}
