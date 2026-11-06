<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.contact-messages.index', compact('messages'));
    }

    public function handle(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'handled']);

        return back()->with('success', 'Message marked as handled.');
    }
}
