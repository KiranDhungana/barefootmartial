<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Branch::query()->firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main branch']
        );
    }
}
