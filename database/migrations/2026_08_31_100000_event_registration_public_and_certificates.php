<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('event_registrations', 'registrant_name')) {
                $table->string('registrant_name')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('event_registrations', 'phone')) {
                $table->string('phone', 50)->nullable()->after('registrant_name');
            }
            if (! Schema::hasColumn('event_registrations', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('event_registrations', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (! Schema::hasColumn('event_registrations', 'certificate_url')) {
                $table->string('certificate_url')->nullable()->after('certificate_number');
            }
            if (! Schema::hasColumn('event_registrations', 'certificate_public_id')) {
                $table->string('certificate_public_id')->nullable()->after('certificate_url');
            }
            if (! Schema::hasColumn('event_registrations', 'certificate_resource_type')) {
                $table->string('certificate_resource_type', 32)->nullable()->after('certificate_public_id');
            }
            if (! Schema::hasColumn('event_registrations', 'certificate_title')) {
                $table->string('certificate_title')->nullable()->after('certificate_resource_type');
            }
            if (! Schema::hasColumn('event_registrations', 'certificate_issued_on')) {
                $table->date('certificate_issued_on')->nullable()->after('certificate_title');
            }
            if (! Schema::hasColumn('event_registrations', 'certificate_uploaded_by')) {
                $table->foreignId('certificate_uploaded_by')->nullable()->after('certificate_issued_on')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Allow guest public registrations (student_id optional).
        try {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key name may differ on some MySQL hosts.
        }

        DB::statement('ALTER TABLE event_registrations MODIFY student_id BIGINT UNSIGNED NULL');

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });

        // Unique on (event_id, student_id) blocks multiple NULL student_ids on some MySQL versions.
        // Drop and replace with a non-unique index; app enforces one registration per student/event.
        try {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->dropUnique(['event_id', 'student_id']);
            });
        } catch (\Throwable $e) {
            // already dropped
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->index(['event_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('event_registrations', 'certificate_uploaded_by')) {
                $table->dropConstrainedForeignId('certificate_uploaded_by');
            }
            foreach ([
                'certificate_issued_on',
                'certificate_title',
                'certificate_resource_type',
                'certificate_public_id',
                'certificate_url',
                'notes',
                'email',
                'phone',
                'registrant_name',
            ] as $col) {
                if (Schema::hasColumn('event_registrations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
