<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'unit_price',
        'size_options',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'size_options' => 'array',
        'is_active' => 'boolean',
    ];

    public function branchInventories(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function stockForBranch(?int $branchId): int
    {
        if (! $branchId) {
            return 0;
        }

        return (int) $this->branchInventories()
            ->where('branch_id', $branchId)
            ->value('quantity');
    }
}
