<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\CommissionAgreement;
use App\Models\CommissionPaymentNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class CommissionController extends Controller
{
    public function index(): View
    {
        $agreements = CommissionAgreement::where('agent_id', auth()->id())
            ->with(['broker', 'property'])
            ->latest()
            ->paginate(10);

        return view('pages.agent.commission.index', compact('agreements'));
    }

    public function show(CommissionAgreement $agreement): View
    {
        abort_unless((int) $agreement->agent_id === (int) auth()->id(), 403);
        $agreement->load(['agent', 'broker', 'property', 'payments.notes']);

        return view('pages.agent.commission.show', compact('agreement'));
    }

    public function updatePayment(Request $request, CommissionAgreement $agreement): RedirectResponse
    {
        abort_unless((int) $agreement->agent_id === (int) auth()->id(), 403);

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
                'sender_type' => 'agent',
                'sender_id' => auth()->id(),
                'message' => $data['payment_message'],
                'proof_path' => $payment->proof_path,
            ]);
        }

        return redirect()->route('agent.commission.show', $agreement)->with('success', 'Payment status updated successfully.');
    }
}
