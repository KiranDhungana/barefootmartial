<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institute_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name')->nullable();
            $table->string('brand_line1')->nullable();
            $table->string('brand_line2')->nullable();
            $table->string('tagline')->nullable();
            $table->string('footer_tagline')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable()->default('Nepal');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('pan', 64)->nullable();
            $table->string('vat', 64)->nullable();
            $table->string('fonepay_terminal')->nullable();
            $table->string('fonepay_address')->nullable();
            $table->string('qr_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('authorized_signatory')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_profiles');
    }
};
