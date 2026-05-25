<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'DOBOK', 'name' => 'Dobok (Uniform)', 'category' => 'uniform', 'unit_price' => 2500, 'size_options' => ['000', '0', '1', '2', '3', '4', '5', '6', '7']],
            ['code' => 'BELT', 'name' => 'Belt', 'category' => 'belt', 'unit_price' => 500, 'size_options' => ['White', 'Yellow', 'Green', 'Blue', 'Red', 'Black']],
            ['code' => 'GLOVES', 'name' => 'Gloves', 'category' => 'equipment', 'unit_price' => 1200, 'size_options' => ['S', 'M', 'L']],
            ['code' => 'SHIN', 'name' => 'Shin guard', 'category' => 'equipment', 'unit_price' => 1500, 'size_options' => ['S', 'M', 'L', 'XL']],
            ['code' => 'CHEST', 'name' => 'Chest guard', 'category' => 'equipment', 'unit_price' => 2000, 'size_options' => ['S', 'M', 'L']],
        ];

        foreach ($items as $row) {
            InventoryItem::query()->firstOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'unit_price' => $row['unit_price'],
                    'size_options' => $row['size_options'],
                    'low_stock_threshold' => 5,
                ]
            );
        }

        $branch = Branch::query()->first();
        if (! $branch) {
            return;
        }

        foreach (InventoryItem::all() as $item) {
            BranchInventory::query()->firstOrCreate(
                ['branch_id' => $branch->id, 'inventory_item_id' => $item->id],
                ['quantity' => 20]
            );
        }
    }
}
