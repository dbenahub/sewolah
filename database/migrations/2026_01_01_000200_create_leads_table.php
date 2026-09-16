<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('origin')->nullable();
            $table->string('airport')->nullable();
            $table->date('arrival_date')->nullable();
            $table->time('arrival_time')->nullable();
            $table->date('end_date')->nullable();
            $table->string('purpose')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('vehicle_name_snapshot')->nullable();
            $table->string('other_vehicle_model')->nullable();
            $table->unsignedTinyInteger('passengers')->nullable();
            $table->string('luggage')->nullable();
            $table->string('destination')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('consent')->default(false);
            $table->enum('status', ['baru', 'dihubungi', 'quotation_dihantar', 'disahkan', 'batal'])->default('baru');
            $table->string('locale', 5)->default('ms');

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->text('landing_page_url')->nullable();
            $table->text('referrer')->nullable();
            $table->string('fbclid')->nullable();

            $table->timestamp('whatsapp_opened_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
