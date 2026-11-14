<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use App\Services\SalesExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The broker's commission table: one row per sale by their agents.
 * Rows appear when an agent marks a listing or lot as Sold, or are added here.
 */
class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filtered($request);
        $totals = (clone $query)->toBase()->selectRaw('count(*) as sales, coalesce(sum(selling_price), 0) as selling, coalesce(sum(agent_commission), 0) as agent, coalesce(sum(broker_commission), 0) as broker')->first();

        $sales = $query->with(['agent', 'property', 'lot'])->orderByDesc('sold_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $agents = $this->agents();

        return view('pages.broker.commission.index', compact('sales', 'totals', 'agents'));
    }

    public function create(): View
    {
        return view('pages.broker.commission.form', $this->formData(new Sale(['sold_at' => now(), 'status' => 'pending'])));
    }

    public function store(Request $request): RedirectResponse
    {
        Sale::create([...$this->validated($request), 'broker_id' => auth()->id(), 'source' => 'manual']);

        return redirect()->route('broker.commissions.index')->with('success', 'Sale recorded.');
    }

    public function edit(Sale $sale): View
    {
        $this->ensureMine($sale);

        return view('pages.broker.commission.form', $this->formData($sale));
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $this->ensureMine($sale);
        $sale->update($this->validated($request));

        return redirect()->route('broker.commissions.index')->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $this->ensureMine($sale);
        $sale->delete();

        return redirect()->route('broker.commissions.index')->with('success', 'Sale removed from the commission table.');
    }

    /** Downloads the rows on screen, with every page, as an Excel file. */
    public function export(Request $request, SalesExport $export): BinaryFileResponse
    {
        $sales = $this->filtered($request)->with(['agent', 'property', 'lot'])->orderByDesc('sold_at')->orderByDesc('id')->get();

        return $export->download($sales, auth()->user()->name, 'commission-records-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function filtered(Request $request): Builder
    {
        return Sale::forBroker(auth()->user())
            ->when($request->filled('agent'), fn ($query) => $query->where('agent_id', $request->integer('agent')))
            ->when(array_key_exists((string) $request->input('status'), Sale::STATUSES), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), fn ($query) => $query->whereHas('property', fn ($property) => $property->withTrashed()
                ->where('name', 'like', '%' . $request->input('search') . '%')));
    }

    private function agents()
    {
        return User::where('role', 'agent')->where('broker_id', auth()->id())->orderBy('name')->get(['id', 'name']);
    }

    private function formData(Sale $sale): array
    {
        $agents = $this->agents();
        $properties = Property::whereIn('broker_id', $agents->pluck('id'))
            ->with(['lots' => fn ($query) => $query->orderBy('lot_number')])
            ->orderBy('name')
            ->get(['id', 'name', 'broker_id', 'price']);

        return compact('sale', 'agents', 'properties');
    }

    private function validated(Request $request): array
    {
        $agentIds = $this->agents()->pluck('id');
        $propertyIds = Property::whereIn('broker_id', $agentIds)->pluck('id');

        $data = $request->validate([
            'agent_id' => ['required', Rule::in($agentIds->all())],
            'property_id' => ['required', Rule::in($propertyIds->all())],
            'lot_id' => ['nullable', Rule::exists('lots', 'id')->where('property_id', $request->input('property_id'))->whereNull('deleted_at')],
            'sold_at' => ['required', 'date', 'before_or_equal:today'],
            'selling_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'agent_commission' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'broker_commission' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'status' => ['required', Rule::in(array_keys(Sale::STATUSES))],
            'show_status_to_agent' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'agent_id.in' => 'Choose one of your agents.',
            'property_id.in' => "Choose one of your agents' listings.",
            'lot_id.exists' => 'That lot is not part of the chosen listing.',
            'sold_at.before_or_equal' => 'The date of sale cannot be in the future.',
        ]);

        $data['show_status_to_agent'] = $request->boolean('show_status_to_agent');

        // The form is the whole record: an optional field left out is cleared, not kept.
        return array_merge(array_fill_keys(['lot_id', 'selling_price', 'agent_commission', 'broker_commission', 'notes'], null), $data);
    }

    /** Another broker's sale looks like it does not exist. */
    private function ensureMine(Sale $sale): void
    {
        abort_unless((int) $sale->broker_id === (int) auth()->id(), 404);
    }
}
