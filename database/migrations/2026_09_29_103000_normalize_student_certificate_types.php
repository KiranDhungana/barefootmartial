<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('student_certificates')) {
            return;
        }

        DB::table('student_certificates')
            ->where(function ($q) {
                $q->where('certificate_type', 'general')
                    ->orWhereNull('certificate_type')
                    ->orWhere('certificate_type', '');
            })
            ->update(['certificate_type' => 'normal']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_certificates')) {
            return;
        }

        DB::table('student_certificates')
            ->where('certificate_type', 'normal')
            ->update(['certificate_type' => 'general']);
    }
};
