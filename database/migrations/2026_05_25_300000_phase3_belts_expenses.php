<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('belt_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('from_belt', 64)->nullable();
            $table->string('to_belt', 64);
            $table->date('promoted_at');
            $table->boolean('exam_passed')->default(true);
            $table->string('certificate_number')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('category', 64);
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('description')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('belt_promotions');
    }
};
