<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_certificates', function (Blueprint $table) {
            if (! Schema::hasColumn('student_certificates', 'certificate_type')) {
                $table->string('certificate_type', 32)->default('general')->after('title');
                $table->index(['student_id', 'certificate_type']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_certificates', function (Blueprint $table) {
            if (Schema::hasColumn('student_certificates', 'certificate_type')) {
                $table->dropIndex(['student_id', 'certificate_type']);
                $table->dropColumn('certificate_type');
            }
        });
    }
};
