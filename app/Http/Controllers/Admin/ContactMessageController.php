<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    /** Status tabs; new messages come first because they still need a reply. */
    public const TABS = ContactMessage::STATUSES + ['all' => 'All'];

    public function index(Request $request): View
    {
        $status = array_key_exists((string) $request->input('status'), self::TABS) ? $request->input('status') : 'new';
        $subject = in_array($request->input('subject'), ContactMessage::SUBJECTS, true) ? $request->input('subject') : null;
        $search = trim((string) $request->input('q'));

        $scope = fn () => ContactMessage::query()
            ->when($subject, fn ($query) => $query->where('subject', $subject))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%")));

        $counts = $scope()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['all'] = $counts->sum();

        $messages = $scope()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.contact-messages.index', [
            'messages' => $messages,
            'tabs' => self::TABS,
            'status' => $status,
            'counts' => $counts,
            'subjects' => ContactMessage::SUBJECTS,
            'subject' => $subject,
            'search' => $search,
        ]);
    }

    public function handle(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'handled']);

        return back()->with('success', "Message from {$contactMessage->fullName()} marked as handled.");
    }

    public function reopen(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'new']);

        return back()->with('success', "Message from {$contactMessage->fullName()} moved back to New.");
    }
}
