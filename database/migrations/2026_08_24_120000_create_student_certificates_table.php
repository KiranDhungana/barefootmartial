<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_url');
            $table->string('public_id')->nullable();
            $table->string('resource_type', 32)->default('image');
            $table->string('original_filename')->nullable();
            $table->date('issued_on')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'issued_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_certificates');
    }
};
