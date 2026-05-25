<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryItem;
use App\Services\InventoryService;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $branches = $user->isSuperAdmin()
            ? Branch::query()->orderBy('name')->get()
            : Branch::query()->where('id', $user->branch_id)->get();

        $branchId = $request->integer('branch_id') ?: $branches->first()?->id;
        if ($user->isBranchScoped()) {
            $branchId = $user->branch_id;
        }

        $items = InventoryItem::query()->where('is_active', true)->orderBy('name')->get();
        $stocks = BranchInventory::query()
            ->with('inventoryItem')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->keyBy('inventory_item_id');

        $lowStock = $this->inventory->lowStockAlerts($branchId);

        return view('erp.inventory.index', compact('items', 'stocks', 'branches', 'branchId', 'lowStock'));
    }

    public function adjustStock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $user = auth()->user();
        if ($user->isBranchScoped() && (int) $data['branch_id'] !== (int) $user->branch_id) {
            abort(403);
        }

        BranchInventory::query()->updateOrCreate(
            [
                'branch_id' => $data['branch_id'],
                'inventory_item_id' => $data['inventory_item_id'],
            ],
            ['quantity' => $data['quantity']]
        );

        return back()->with('success', 'Stock updated.');
    }

    public function transferForm(): View
    {
        $user = auth()->user();
        $branches = Branch::query()->orderBy('name')->get();
        if ($user->isBranchScoped()) {
            $branches = Branch::query()->where('id', $user->branch_id)->get();
        }
        $items = InventoryItem::query()->where('is_active', true)->orderBy('name')->get();

        return view('erp.inventory.transfer', compact('branches', 'items'));
    }

    public function transfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        if ($user->isBranchScoped() && (int) $data['from_branch_id'] !== (int) $user->branch_id) {
            abort(403);
        }

        try {
            $this->inventory->transfer(
                (int) $data['from_branch_id'],
                (int) $data['to_branch_id'],
                (int) $data['inventory_item_id'],
                (int) $data['quantity'],
                $data['notes'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('erp.inventory.index', ['branch_id' => $data['from_branch_id']])
            ->with('success', 'Stock transferred.');
    }
}
