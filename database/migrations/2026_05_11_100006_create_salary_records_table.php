<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('attendance_days')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['trainer_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
