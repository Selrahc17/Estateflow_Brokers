<?php

namespace App\Http\Controllers\Client;

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
        $client = auth()->user()->clientProfile;
        $payments = $client
            ? Payment::where('client_id', $client->id)->with('reservation')->latest()->paginate(10)
            : collect();

        return view('pages.client.payments.index', compact('payments'));
    }

    public function pay(): View
    {
        $client = auth()->user()->clientProfile;
        $reservations = $client
            ? $client->reservations()->whereIn('status', ['pending', 'confirmed'])->with('lot.property')->get()
            : collect();

        return view('pages.client.pay.index', compact('reservations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $client = auth()->user()->clientProfile;

        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount'         => 'required|numeric|min:1',
            'payment_type'   => 'required|in:down_payment,monthly,full,other',
            'payment_method' => 'required|in:cash,bank_transfer,GCash,Maya,check',
            'reference_number' => 'nullable|string',
        ]);

        $data['client_id'] = $client->id;
        $data['payment_code'] = 'PAY-' . strtoupper(Str::random(8));
        $data['status'] = 'pending';

        Payment::create($data);

        return redirect()->route('client.account.payments')->with('success', 'Payment submitted. Awaiting verification.');
    }
}