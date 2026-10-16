<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->string('pic_name', 80)->nullable();
            $table->string('bank_name', 80)->nullable();
            $table->string('bank_account_name', 120)->nullable();
            $table->string('bank_account_number', 40)->nullable();
            $table->string('duitnow_qr_path')->nullable();
            $table->json('pricing')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropColumn(['pic_name', 'bank_name', 'bank_account_name', 'bank_account_number', 'duitnow_qr_path', 'pricing']);
        });
    }
};
