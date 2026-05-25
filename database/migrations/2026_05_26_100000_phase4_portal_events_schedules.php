<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('email');
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('coach_name')->nullable();
            $table->string('day_of_week', 16);
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('belt_level', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['parent_user_id', 'student_id']);
        });

        Schema::create('online_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_name');
            $table->string('parent_name')->nullable();
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 32)->default('pending');
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->date('registration_deadline')->nullable();
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('category', 128)->nullable();
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->string('status', 32)->default('registered');
            $table->string('certificate_number')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'student_id']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('online_registrations');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('class_schedules');
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'is_active']);
        });
    }
};
