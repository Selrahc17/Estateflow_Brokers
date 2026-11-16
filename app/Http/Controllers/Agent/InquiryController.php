<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Support\Totals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $mine = fn () => Inquiry::where('broker_id', auth()->id());
        $status = array_key_exists((string) $request->input('status'), Inquiry::STATUSES) ? $request->input('status') : null;
        $type = in_array($request->input('type'), ['inquiry', 'viewing'], true) ? $request->input('type') : null;
        $search = trim((string) $request->input('q'));

        $statusCounts = $mine()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $stats = Totals::of([
            'total' => $mine()->selectRaw('count(*)'),
            'new' => $mine()->where('status', 'new')->selectRaw('count(*)'),
            'viewings' => $mine()->where('type', 'viewing')->whereIn('status', ['new', 'contacted'])->selectRaw('count(*)'),
            'this_month' => $mine()->where('created_at', '>=', now()->startOfMonth())->selectRaw('count(*)'),
        ]);

        $inquiries = $mine()
            ->with(['user', 'property'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . mb_strtolower($search) . '%';
                $query->where(fn ($match) => $match
                    ->whereRaw('lower(contact_name) like ?', [$like])
                    ->orWhereRaw('lower(email) like ?', [$like])
                    ->orWhereRaw('lower(phone) like ?', [$like])
                    ->orWhereHas('property', fn ($property) => $property->whereRaw('lower(name) like ?', [$like])));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.agent.inquiries.index', compact('inquiries', 'statusCounts', 'stats', 'status', 'type', 'search'));
    }

    public function show(Inquiry $inquiry): View
    {
        abort_if($inquiry->broker_id !== auth()->id(), 403);

        $client = Client::where('email', $inquiry->email)->where('broker_id', auth()->id())->first();

        return view('pages.agent.inquiries.show', compact('inquiry', 'client'));
    }

    public function convertToClient(Inquiry $inquiry): RedirectResponse
    {
        abort_if($inquiry->broker_id !== auth()->id(), 403);

        // Each agent keeps their own record of a buyer, so only this agent's records matter here.
        $mine = Client::withTrashed()->where('email', $inquiry->email)->where('broker_id', auth()->id())->first();

        if ($mine?->trashed()) {
            // The agent removed this buyer before; adding them again brings back their old record.
            $mine->restore();

            return redirect()->route('agent.clients.show', $mine)->with('success', 'Client restored.');
        }

        if ($mine) {
            return redirect()->route('agent.clients.show', $mine);
        }

        // A buyer who was signed in may have a record from sign-up with no agent yet. Claim that one.
        // A typed email alone never links a record to someone's account.
        if ($inquiry->user_id) {
            $unclaimed = Client::where('user_id', $inquiry->user_id)->whereNull('broker_id')->value('id');

            if ($unclaimed && Client::whereKey($unclaimed)->whereNull('broker_id')->update(['broker_id' => auth()->id()])) {
                return redirect()->route('agent.clients.show', $unclaimed)->with('success', 'Inquiry added to your clients.');
            }
        }

        [$firstName, $lastName] = $this->nameParts($inquiry);

        $client = Client::create([
            'user_id' => $inquiry->user_id,
            'broker_id' => auth()->id(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone,
            'password' => Hash::make(Str::random(24)),
            'status' => 'active',
        ]);

        return redirect()->route('agent.clients.show', $client)->with('success', 'Inquiry added to your clients.');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function nameParts(Inquiry $inquiry): array
    {
        $name = trim((string) ($inquiry->contact_name ?: $inquiry->user?->name));

        if ($name === '') {
            $name = str_replace(['.', '_', '-'], ' ', Str::before((string) $inquiry->email, '@'));
        }

        $parts = preg_split('/\s+/', trim($name), 2) ?: [''];

        return [Str::ucfirst($parts[0] ?: 'Client'), Str::ucfirst($parts[1] ?? '')];
    }

    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_if($inquiry->broker_id !== auth()->id(), 403);

        $validated = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(array_keys(Inquiry::STATUSES))],
        ]);

        $inquiry->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Inquiry status updated successfully.');
    }
}
