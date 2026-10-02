<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('source', 30)->default('outstation')->after('id');
            $table->string('customer_category', 40)->nullable()->after('source');
            $table->string('company_name')->nullable()->after('email');
            $table->string('pickup_state', 60)->nullable()->after('origin');
            $table->string('pickup_location')->nullable()->after('pickup_state');
            $table->string('return_location')->nullable()->after('pickup_location');
            $table->string('driver_license', 30)->nullable()->after('company_name');

            $table->index(['source']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn([
                'source', 'customer_category', 'company_name', 'pickup_state',
                'pickup_location', 'return_location', 'driver_license',
            ]);
        });
    }
};
