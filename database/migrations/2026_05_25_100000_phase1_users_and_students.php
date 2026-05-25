<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('role')->constrained()->nullOnDelete();
            }
        });

        DB::table('users')->where('is_admin', 1)->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);

        Schema::table('students', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('address');
            $table->string('gender', 16)->nullable()->after('dob');
            $table->string('blood_group', 8)->nullable()->after('gender');
            $table->string('parent_name')->nullable()->after('blood_group');
            $table->string('parent_contact', 50)->nullable()->after('parent_name');
            $table->string('emergency_contact', 50)->nullable()->after('parent_contact');
            $table->string('coach_name')->nullable()->after('emergency_contact');
            $table->string('belt_rank', 64)->nullable()->after('coach_name');
            $table->string('batch_timing', 128)->nullable()->after('belt_rank');
            $table->string('status', 32)->default('active')->after('batch_timing');
            $table->string('registration_status', 32)->default('pending')->after('status');
            $table->string('fee_status', 64)->nullable()->after('registration_status');
            $table->string('uniform_status', 64)->nullable()->after('fee_status');
            $table->timestamp('registered_at')->nullable()->after('uniform_status');
            $table->foreignId('registered_by')->nullable()->after('registered_at')->constrained('users')->nullOnDelete();
            $table->boolean('imported')->default(false)->after('registered_by');
        });

        DB::table('students')->update([
            'registration_status' => 'official',
            'registered_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['registered_by']);
            $table->dropColumn([
                'dob', 'gender', 'blood_group', 'parent_name', 'parent_contact',
                'emergency_contact', 'coach_name', 'belt_rank', 'batch_timing',
                'status', 'registration_status', 'fee_status', 'uniform_status',
                'registered_at', 'registered_by', 'imported',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
        });
    }
};
