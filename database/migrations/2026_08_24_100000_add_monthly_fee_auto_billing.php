<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'monthly_fee')) {
                $table->decimal('monthly_fee', 12, 2)->default(0)->after('discount_percent');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'billing_period')) {
                $table->string('billing_period', 32)->nullable()->after('notes');
                $table->unique(['student_id', 'billing_period']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'billing_period')) {
                $table->dropUnique(['student_id', 'billing_period']);
                $table->dropColumn('billing_period');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'monthly_fee')) {
                $table->dropColumn('monthly_fee');
            }
        });
    }
};
