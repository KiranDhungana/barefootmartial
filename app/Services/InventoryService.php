<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function findItem(int $id): InventoryItem
    {
        return InventoryItem::query()->where('is_active', true)->findOrFail($id);
    }

    public function deduct(?int $branchId, int $itemId, int $qty): void
    {
        if (! $branchId || $qty <= 0) {
            return;
        }

        $stock = BranchInventory::query()
            ->where('branch_id', $branchId)
            ->where('inventory_item_id', $itemId)
            ->lockForUpdate()
            ->first();

        if (! $stock || $stock->quantity < $qty) {
            $item = InventoryItem::find($itemId);
            throw ValidationException::withMessages([
                'inventory' => 'Insufficient stock for '.($item->name ?? 'item').' (have '.($stock->quantity ?? 0).', need '.$qty.').',
            ]);
        }

        $stock->decrement('quantity', $qty);
    }

    public function addStock(int $branchId, int $itemId, int $qty): BranchInventory
    {
        $stock = BranchInventory::query()->firstOrCreate(
            ['branch_id' => $branchId, 'inventory_item_id' => $itemId],
            ['quantity' => 0]
        );
        $stock->increment('quantity', $qty);

        return $stock->fresh();
    }

    public function transfer(
        int $fromBranchId,
        int $toBranchId,
        int $itemId,
        int $qty,
        ?string $notes = null,
        ?User $by = null
    ): StockTransfer {
        if ($fromBranchId === $toBranchId) {
            throw ValidationException::withMessages(['branch' => 'Cannot transfer to the same branch.']);
        }

        return DB::transaction(function () use ($fromBranchId, $toBranchId, $itemId, $qty, $notes, $by) {
            $this->deduct($fromBranchId, $itemId, $qty);
            $this->addStock($toBranchId, $itemId, $qty);

            $transfer = StockTransfer::create([
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'inventory_item_id' => $itemId,
                'quantity' => $qty,
                'notes' => $notes,
                'created_by' => $by?->id ?? auth()->id(),
            ]);

            AuditLogger::log('inventory.transfer', $transfer, null, $transfer->getAttributes());

            return $transfer;
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, InventoryItem>
     */
    public function itemsWithStock(?int $branchId)
    {
        return InventoryItem::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function (InventoryItem $item) use ($branchId) {
                $item->setAttribute('stock_quantity', $item->stockForBranch($branchId));

                return $item;
            });
    }

    /**
     * @return list<array{item: InventoryItem, branch: Branch, quantity: int}>
     */
    public function lowStockAlerts(?int $branchId = null): array
    {
        $q = BranchInventory::query()
            ->with(['inventoryItem', 'branch'])
            ->whereHas('inventoryItem', fn ($qq) => $qq->where('is_active', true));

        if ($branchId) {
            $q->where('branch_id', $branchId);
        }

        $alerts = [];
        foreach ($q->get() as $stock) {
            $threshold = $stock->inventoryItem->low_stock_threshold;
            if ($stock->quantity <= $threshold) {
                $alerts[] = [
                    'item' => $stock->inventoryItem,
                    'branch' => $stock->branch,
                    'quantity' => $stock->quantity,
                ];
            }
        }

        return $alerts;
    }
}
