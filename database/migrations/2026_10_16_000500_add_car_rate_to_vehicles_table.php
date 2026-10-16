<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('car_rate_id')->nullable()->after('category')->constrained('car_rates')->nullOnDelete();
        });

        // Link existing website vehicles to their price-list entry by name.
        $rates = DB::table('car_rates')->get(['id', 'brand', 'model']);
        foreach (DB::table('vehicles')->get(['id', 'name']) as $vehicle) {
            $name = mb_strtolower($vehicle->name);
            $match = $rates->first(fn ($r) => str_contains($name, mb_strtolower($r->model))
                && str_contains($name, mb_strtolower(explode('-', $r->brand)[0])));
            if ($match) {
                DB::table('vehicles')->where('id', $vehicle->id)->update(['car_rate_id' => $match->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('car_rate_id');
        });
    }
};
