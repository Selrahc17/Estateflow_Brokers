<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\BrokerComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrokerController extends Controller
{
    public function index(): View
    {
        $brokers = User::where('role', 'broker')
            ->when(request('search'), fn($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('email', 'like', '%'.request('search').'%');
            }))
            ->when(request('approval_status'), fn($q) => $q->where('is_approved', request('approval_status') === 'approved'))
            ->withCount('clients')
            ->latest()
            ->paginate(15);

        return view('pages.admin.brokers.index', compact('brokers'));
    }

    public function create(): View
    {
        return view('pages.admin.brokers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'license_number' => 'required|string|max:100|unique:users,license_number',
            'avatar' => 'required|image|max:2048',
        ]);

        $file = $request->file('avatar');
        $filename = 'broker-avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
        Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
        $data['avatar'] = env('SUPABASE_URL') . '/storage/v1/object/public/properties/' . $filename;

        $data['role'] = 'broker';
        $data['is_approved'] = false; // New brokers need approval
        User::create($data);

        return redirect()->route('admin.brokers')->with('success', 'Broker created successfully. Pending approval.');
    }

    public function show(User $user, BrokerComplianceService $compliance): View
    {
        $this->ensureBroker($user);
        $user->load('clients');
        $checklist = $compliance->checklist($user);
        $outstanding = $compliance->outstanding($user);

        return view('pages.admin.brokers.show', compact('user', 'checklist', 'outstanding'));
    }

    public function approve(User $user, BrokerComplianceService $compliance): RedirectResponse
    {
        $this->ensureBroker($user);

        $outstanding = $compliance->outstanding($user);

        if ($outstanding) {
            return back()->withErrors(['approval' => "{$user->name} cannot be approved yet: ".implode('; ', $outstanding).'.']);
        }

        $user->update(['is_approved' => true]);

        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'broker_approved',
            'title' => 'Your broker account is approved',
            'message' => 'You now have full access to your broker portal.',
            'link' => route('broker.dashboard'),
            'is_read' => false,
        ]);

        return back()->with('success', "Broker {$user->name} has been approved!");
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->ensureBroker($user);

        $data = $request->validate(
            ['reason' => ['required', 'string', 'max:1000']],
            ['reason.required' => 'Please give a reason so the broker knows what to fix.']
        );

        $user->update(['is_approved' => false]);

        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'broker_rejected',
            'title' => 'Your broker application needs changes',
            'message' => $data['reason'],
            'is_read' => false,
        ]);

        return back()->with('success', "Broker {$user->name} has been rejected.");
    }

    private function ensureBroker(User $user): void
    {
        abort_unless($user->role === 'broker', 404);
    }
}