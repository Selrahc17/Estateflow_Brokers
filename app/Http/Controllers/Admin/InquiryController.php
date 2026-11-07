<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'site_visit_scheduled' => 'Site visit scheduled',
        'negotiating' => 'Negotiating',
        'closed' => 'Closed',
        'lost' => 'Lost',
    ];

    public function index(): View
    {
        $brokers = User::where('role', 'broker')->orderBy('name')->get(['id', 'name']);

        $inquiries = Inquiry::query()
            ->when(request('search'), function ($query) {
                $search = '%'.request('search').'%';
                $query->where(fn ($q) => $q->where('contact_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhereHas('property', fn ($property) => $property->where('name', 'like', $search)));
            })
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->when(request('broker'), fn ($query) => $query->whereHas('broker', fn ($agent) => $agent->where('broker_id', request('broker'))))
            ->with(['broker:id,name,broker_id', 'broker.broker:id,name', 'property:id,name', 'user:id,name'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.inquiries.index', [
            'inquiries' => $inquiries,
            'brokers' => $brokers,
            'statuses' => self::STATUSES,
        ]);
    }
}
