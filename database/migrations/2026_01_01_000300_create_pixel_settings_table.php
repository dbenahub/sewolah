<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pixel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('meta_pixel_id')->nullable();
            $table->string('google_ads_id')->nullable();
            $table->string('tiktok_pixel_id')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pixel_settings');
    }
};
