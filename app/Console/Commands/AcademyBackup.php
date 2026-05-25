<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AcademyBackup extends Command
{
    protected $signature = 'academy:backup';

    protected $description = 'Export core academy tables to JSON in storage/app/backups';

    public function handle(): int
    {
        $dir = config('academy.backup_path', storage_path('app/backups'));
        File::ensureDirectoryExists($dir);

        $tables = [
            'branches', 'users', 'students', 'invoices', 'invoice_line_items', 'payments',
            'attendances', 'belt_promotions', 'expenses', 'inventory_items', 'branch_inventories',
            'stock_transfers', 'events', 'event_registrations', 'class_schedules',
            'online_registrations', 'parent_student', 'notification_logs', 'audit_logs',
        ];

        $export = ['backed_up_at' => now()->toIso8601String(), 'tables' => []];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $export['tables'][$table] = DB::table($table)->get();
            }
        }

        $path = $dir.DIRECTORY_SEPARATOR.'backup-'.now()->format('Y-m-d-His').'.json';
        File::put($path, json_encode($export, JSON_PRETTY_PRINT));

        $this->info('Backup saved: '.$path);

        return self::SUCCESS;
    }
}
