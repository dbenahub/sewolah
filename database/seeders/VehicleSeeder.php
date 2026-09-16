<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'name' => 'Proton X70',
                'category' => 'FAMILY SUV',
                'image_path' => 'vehicles/car-x70.jpg',
                'cta_label' => 'TEMPAH X70',
                'sort_order' => 1,
                'tags' => [
                    'ms' => ['Family trip', 'Small to medium family', 'Business traveller', 'Multi-day travel', 'Comfortable SUV travel'],
                    'en' => ['Family trip', 'Small to medium family', 'Business traveller', 'Multi-day travel', 'Comfortable SUV travel'],
                ],
            ],
            [
                'name' => 'Volkswagen Arteon R-Line',
                'category' => 'EXECUTIVE DRIVE',
                'image_path' => 'vehicles/car-arteon.jpg',
                'cta_label' => 'TEMPAH ARTEON',
                'sort_order' => 2,
                'tags' => [
                    'ms' => ['Business traveller', 'Executive', 'SME owner', 'Corporate guest', 'Special occasion'],
                    'en' => ['Business traveller', 'Executive', 'SME owner', 'Corporate guest', 'Special occasion'],
                ],
            ],
            [
                'name' => 'Toyota Alphard SC',
                'category' => 'PREMIUM FAMILY MPV',
                'image_path' => 'vehicles/car-alphard.jpg',
                'cta_label' => 'TEMPAH ALPHARD',
                'sort_order' => 3,
                'tags' => [
                    'ms' => ['Large family', 'Parents and children', 'More luggage', 'Premium traveller', 'Corporate / VIP guest'],
                    'en' => ['Large family', 'Parents and children', 'More luggage', 'Premium traveller', 'Corporate / VIP guest'],
                ],
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(['name' => $vehicle['name']], $vehicle);
        }
    }
}
