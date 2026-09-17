<?php

namespace Tests\Feature;

use App\Mail\CustomerBookingConfirmation;
use App\Mail\NewLeadNotification;
use App\Models\Lead;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use App\Livewire\Public\BookingForm;
use Tests\TestCase;

class BookingFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_is_created_after_full_submission(): void
    {
        Mail::fake();

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
            ->set('vehicleId', (string) $vehicle->id)
            ->set('passengers', 4)
            ->set('luggage', 'Medium')
            ->set('destination', 'KLCC')
            ->call('goNext')
            ->set('fullName', 'Ahmad Test')
            ->set('phone', '0123456789')
            ->set('email', 'ahmad@example.com')
            ->set('consent', true)
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'Ahmad Test',
            'phone' => '0123456789',
            'email' => 'ahmad@example.com',
            'status' => 'baru',
        ]);

        Mail::assertSent(NewLeadNotification::class);
        Mail::assertSent(CustomerBookingConfirmation::class);
    }

    public function test_other_vehicle_requires_model_name(): void
    {
        Mail::fake();

        Livewire::test(BookingForm::class)
            ->set('origin', 'Johor Bahru')
            ->set('airport', 'KLIA')
            ->set('purpose', 'Family Trip')
            ->set('arrivalDate', now()->addDays(3)->toDateString())
            ->set('arrivalTime', '10:00')
            ->set('endDate', now()->addDays(6)->toDateString())
            ->call('goNext')
            ->set('vehicleId', 'other')
            ->set('passengers', 2)
            ->set('luggage', 'Light')
            ->set('destination', 'Bangsar')
            ->call('goNext')
            ->assertHasErrors(['otherVehicleModel' => 'required']);

        Livewire::test(BookingForm::class)
            ->set('origin', 'Johor Bahru')
            ->set('airport', 'KLIA')
            ->set('purpose', 'Family Trip')
            ->set('arrivalDate', now()->addDays(3)->toDateString())
            ->set('arrivalTime', '10:00')
            ->set('endDate', now()->addDays(6)->toDateString())
            ->call('goNext')
            ->set('vehicleId', 'other')
            ->set('otherVehicleModel', 'Honda CR-V')
            ->set('passengers', 2)
            ->set('luggage', 'Light')
            ->set('destination', 'Bangsar')
            ->call('goNext')
            ->set('fullName', 'Siti Test')
            ->set('phone', '0123456789')
            ->set('email', 'siti@example.com')
            ->set('consent', true)
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'Siti Test',
            'vehicle_id' => null,
            'vehicle_name_snapshot' => 'Honda CR-V',
        ]);
    }
}
