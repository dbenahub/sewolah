<?php

use App\Support\CarCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rates', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 60);
            $table->string('model', 120);
            $table->string('category', 30)->default('sedan');
            $table->unsignedInteger('agent_rate')->nullable();   // harga ejen (admin sahaja)
            $table->unsignedInteger('vip_rate')->nullable();     // harga ejen VIP (admin sahaja)
            $table->unsignedInteger('retail_min');               // harga booking (tawaran)
            $table->unsignedInteger('retail_max')->nullable();   // harga atas julat retail
            $table->unsignedInteger('list_price')->nullable();   // harga asal (kosong = auto)
            $table->unsignedInteger('deposit')->nullable();      // deposit (kosong = ikut kategori)
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'brand']);
        });

        $now = now();
        $rows = [];
        foreach (CarCatalog::DEFAULTS as $i => [$brand, $model, $category, $agent, $vip, $min, $max]) {
            $rows[] = [
                'brand' => $brand, 'model' => $model, 'category' => $category,
                'agent_rate' => $agent, 'vip_rate' => $vip,
                'retail_min' => $min, 'retail_max' => $max,
                'is_active' => true, 'sort_order' => ($i + 1) * 10,
                'created_at' => $now, 'updated_at' => $now,
            ];
        }
        DB::table('car_rates')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rates');
    }
};
