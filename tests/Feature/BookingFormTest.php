<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Public\BookingForm;
use Tests\TestCase;

class BookingFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_is_created_after_full_submission(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Proton X70', 'category' => 'Family SUV',
            'tags' => ['ms' => [], 'en' => []], 'is_featured' => true, 'is_active' => true,
        ]);

        Livewire::test(BookingForm::class)
            ->set('origin', 'Johor Bahru')
            ->set('airport', 'KLIA')
            ->set('purpose', 'Family Trip')
            ->set('arrivalDate', now()->addDays(3)->toDateString())
            ->set('arrivalTime', '10:00')
            ->set('endDate', now()->addDays(6)->toDateString())
            ->call('goNext')
            ->set('vehicleId', $vehicle->id)
            ->set('passengers', 4)
            ->set('luggage', 'Medium')
            ->set('destination', 'KLCC')
            ->call('goNext')
            ->set('fullName', 'Ahmad Test')
            ->set('phone', '0123456789')
            ->set('consent', true)
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'Ahmad Test',
            'phone' => '0123456789',
            'status' => 'baru',
        ]);
    }
}
