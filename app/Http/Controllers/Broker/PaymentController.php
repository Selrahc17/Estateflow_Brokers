<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::where('broker_id', auth()->id())
            ->with(['client', 'reservation'])
            ->latest()
            ->paginate(15);

        return view('pages.payments.index', compact('payments'));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => 'verified',
            'paid_at' => now(),
        ]);

        return redirect()->route('broker.payments.index')->with('success', 'Payment verified successfully.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate(['notes' => 'nullable|string']);
        $payment->update(['status' => 'failed', 'notes' => $request->notes]);

        return redirect()->route('broker.payments.index')->with('success', 'Payment rejected.');
    }
}