<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Document;
use App\Models\User;
use App\Services\BrokerComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /** Status tabs, in the order an admin works through them. */
    public const TABS = Document::STATUSES + ['all' => 'All'];

    /**
     * A review queue of broker and agent documents, grouped by broker.
     * Brokers upload their own files; admins only review them.
     */
    public function index(Request $request, BrokerComplianceService $compliance): View
    {
        $status = array_key_exists((string) $request->input('status'), self::TABS) ? $request->input('status') : 'pending';
        $types = Document::typeLabels();
        $type = array_key_exists((string) $request->input('type'), $types) ? $request->input('type') : null;
        $search = trim((string) $request->input('q'));

        // Buyer requirements are handled by the developer's coordinator, so older
        // buyer uploads stay stored but are not shown here.
        $scope = fn () => Document::whereNull('client_id')
            ->whereNotNull('broker_id')
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->whereHas('broker', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('agent', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))));

        $counts = $scope()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['all'] = $counts->sum();

        $filtered = $scope()->when($status !== 'all', fn ($query) => $query->where('status', $status));

        $owners = User::whereIn('id', (clone $filtered)->select('broker_id')->toBase())
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $complianceOrder = array_flip(array_keys(Document::BROKER_COMPLIANCE_TYPES));
        $documents = (clone $filtered)
            ->whereIn('broker_id', $owners->pluck('id'))
            ->with('agent:id,name')
            ->get()
            ->sortBy(fn (Document $document) => [
                $document->agent_id ? 1 : 0,
                $complianceOrder[$document->type] ?? 99,
                $document->created_at?->timestamp ?? 0,
            ])
            ->groupBy('broker_id');

        $checklists = $owners->filter(fn (User $owner) => $owner->role === 'broker')
            ->mapWithKeys(fn (User $broker) => [$broker->id => $compliance->checklist($broker)]);

        return view('pages.admin.documents.index', [
            'tabs' => self::TABS,
            'status' => $status,
            'counts' => $counts,
            'types' => $types,
            'type' => $type,
            'search' => $search,
            'owners' => $owners,
            'documents' => $documents,
            'checklists' => $checklists,
        ]);
    }

    public function download(Document $document)
    {
        if (str_starts_with($document->file_path, 'http')) {
            return redirect()->away($document->file_path);
        }

        $filename = $document->name.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($document->file_path)) {
                return Storage::disk($disk)->download($document->file_path, $filename);
            }
        }

        abort(404);
    }

    public function verify(Document $document): RedirectResponse
    {
        $document->update(['status' => 'verified', 'verified_at' => now(), 'notes' => null]);
        $this->notifyOwner($document, "{$document->name} was verified.");

        return back()->with('success', "{$document->name} verified.");
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $notes = $this->reason($request);
        $document->update(['status' => 'rejected', 'notes' => $notes, 'verified_at' => null]);
        $this->notifyOwner($document, "{$document->name} was rejected: {$notes}");

        return back()->with('success', "{$document->name} rejected. The broker has been told why.");
    }

    public function requestMore(Request $request, Document $document): RedirectResponse
    {
        $notes = $this->reason($request);
        $document->update(['status' => 'needs_more', 'notes' => $notes, 'verified_at' => null]);
        $this->notifyOwner($document, "More is needed for {$document->name}: {$notes}");

        return back()->with('success', "Asked the broker for a new copy of {$document->name}.");
    }

    private function reason(Request $request): string
    {
        return $request->validate(
            ['notes' => ['required', 'string', 'max:1000']],
            ['notes.required' => 'Please give a reason so the owner knows what to fix.']
        )['notes'];
    }

    private function notifyOwner(Document $document, string $message): void
    {
        // Only broker documents are reviewed here; buyers no longer submit documents.
        if ($document->broker_id && ! $document->client_id) {
            AppNotification::create([
                'user_id' => $document->broker_id,
                'type' => 'document_review',
                'title' => 'Document review update',
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();

        return back()->with('success', "{$document->name} deleted.");
    }
}
