<?php

namespace Tests\Feature;

use App\Livewire\Public\RequestForm;
use App\Mail\CustomerBookingConfirmation;
use App\Mail\NewLeadNotification;
use App\Models\Lead;
use App\Models\PageSetting;
use App\Models\Vehicle;
use App\Support\BookingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CorporateSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function vehicle(): Vehicle
    {
        return Vehicle::create([
            'name' => 'Toyota Alphard SC', 'category' => 'PREMIUM FAMILY MPV',
            'image_path' => 'vehicles/car-alphard.jpg',
            'tags' => ['ms' => [], 'en' => []], 'is_featured' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    public function test_homepage_is_the_new_corporate_site(): void
    {
        $this->vehicle();

        $this->get('/')
            ->assertOk()
            ->assertSee('Untuk Pelanggan', false)
            ->assertSee('Semenanjung Malaysia')
            ->assertSee(route('booking.form'))
            ->assertSee('Toyota Alphard SC')
            ->assertSee('images/home/fleet/car-alphard.jpg')
            ->assertSee('3 hari bekerja');
    }

    public function test_homepage_in_english(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertOk()
            ->assertSee('Selected')
            ->assertSee('Apply to Book');
    }

    public function test_outstation_page_keeps_previous_landing(): void
    {
        $this->get('/outstation')
            ->assertOk()
            ->assertSee('Datang KL Naik Flight', false);
    }

    public function test_form_page_and_short_links(): void
    {
        $this->get('/form')->assertOk()->assertSee('Mohon Tempahan Kenderaan')->assertSee(BookingPolicy::earliestPickupDateLabel());
        $this->get('/borang')->assertRedirect('/form');
        $this->get('/tempah')->assertRedirect('/form');
        $this->get('/form?kategori=corporate')->assertOk()->assertSee('Nama Syarikat');
    }

    public function test_earliest_date_counts_working_days(): void
    {
        // Friday 2 Oct 2026 -> +3 working days = Wednesday 7 Oct 2026
        $this->assertSame('2026-10-07', BookingPolicy::earliestPickupDate(Carbon::parse('2026-10-02 15:00'))->toDateString());
        // Monday 5 Oct 2026 -> Thursday 8 Oct 2026
        $this->assertSame('2026-10-08', BookingPolicy::earliestPickupDate(Carbon::parse('2026-10-05 09:00'))->toDateString());
        // Saturday 3 Oct 2026 -> Wednesday 7 Oct 2026
        $this->assertSame('2026-10-07', BookingPolicy::earliestPickupDate(Carbon::parse('2026-10-03 09:00'))->toDateString());
    }

    protected function fillValidStepsOneAndTwo($component, string $category = 'individual', ?string $vehicleId = null)
    {
        $pickup = BookingPolicy::earliestPickupDateString();

        return $component
            ->call('selectCategory', $category)
            ->set('companyName', $category === 'corporate' ? 'ABC Holdings Sdn Bhd' : '')
            ->call('goNext')
            ->assertSet('step', 2)
            ->set('purpose', 'Percutian')
            ->set('pickupState', 'Pulau Pinang')
            ->set('pickupLocation', 'Lapangan Terbang Antarabangsa Pulau Pinang')
            ->set('pickupDate', $pickup)
            ->set('pickupTime', '09:30')
            ->set('returnDate', Carbon::parse($pickup)->addDays(4)->toDateString())
            ->set('vehicleId', $vehicleId ?? 'any')
            ->set('passengers', '5')
            ->call('goNext')
            ->assertSet('step', 3);
    }

    public function test_general_form_full_submission_sends_emails_and_whatsapp(): void
    {
        Mail::fake();
        PageSetting::current()->update(['admin_notification_email' => 'sewolah.hq@gmail.com', 'whatsapp_number' => '601116946696']);
        $vehicle = $this->vehicle();

        $component = $this->fillValidStepsOneAndTwo(Livewire::test(RequestForm::class), 'corporate', (string) $vehicle->id)
            ->set('fullName', 'Nurul Aisyah Binti Ahmad')
            ->set('phone', '012-345 6789')
            ->set('email', 'aisyah@example.com')
            ->set('driverLicense', 'malaysia')
            ->set('notes', 'Perlukan kerusi kanak-kanak')
            ->set('consent', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertDispatched('lead-submitted');

        $lead = Lead::firstOrFail();
        $this->assertSame('general', $lead->source);
        $this->assertSame('corporate', $lead->customer_category);
        $this->assertSame('ABC Holdings Sdn Bhd', $lead->company_name);
        $this->assertSame('Pulau Pinang', $lead->pickup_state);
        $this->assertSame('Toyota Alphard SC', $lead->vehicle_name_snapshot);
        $this->assertSame($vehicle->id, $lead->vehicle_id);
        $this->assertSame('malaysia', $lead->driver_license);
        $this->assertSame('baru', $lead->status);

        $wa = $component->get('whatsappUrl');
        $this->assertStringStartsWith('https://wa.me/601116946696?text=', $wa);
        $decoded = rawurldecode($wa);
        $this->assertStringContainsString('Nurul Aisyah Binti Ahmad', $decoded);
        $this->assertStringContainsString('Korporat & Syarikat', $decoded);
        $this->assertStringContainsString($lead->referenceNumber(), $decoded);
        $this->assertSame($lead->referenceNumber(), $component->get('reference'));

        Mail::assertSent(NewLeadNotification::class, fn ($m) => $m->hasTo('sewolah.hq@gmail.com'));
        Mail::assertSent(CustomerBookingConfirmation::class, fn ($m) => $m->hasTo('aisyah@example.com'));
    }

    public function test_emails_render_for_general_lead(): void
    {
        $lead = Lead::create([
            'source' => 'general', 'customer_category' => 'event', 'full_name' => 'Test Render',
            'phone' => '0123456789', 'email' => 't@example.com', 'pickup_state' => 'Johor',
            'pickup_location' => 'Johor Bahru', 'arrival_date' => '2026-10-20', 'arrival_time' => '10:00:00',
            'end_date' => '2026-10-21', 'purpose' => 'Majlis / Acara', 'vehicle_name_snapshot' => 'Tiada pilihan khusus',
            'passengers' => 4, 'driver_license' => 'international', 'consent' => true, 'locale' => 'ms', 'submitted_at' => now(),
        ]);

        $admin = (new NewLeadNotification($lead))->render();
        $this->assertStringContainsString('Majlis &amp; Acara Khas', $admin);
        $this->assertStringContainsString('SWL-00001', $admin);
        $this->assertStringContainsString('Johor Bahru', $admin);

        $customer = (new CustomerBookingConfirmation($lead, 'https://wa.me/601116946696'))->render();
        $this->assertStringContainsString('Permohonan tempahan anda telah kami terima', $customer);
        $this->assertStringContainsString('20/10/2026 10:00', $customer);
    }

    public function test_general_form_rejects_last_minute_and_requires_fields(): void
    {
        Livewire::test(RequestForm::class)
            ->call('goNext')
            ->assertHasErrors(['customerCategory'])
            ->call('selectCategory', 'corporate')
            ->call('goNext')
            ->assertHasErrors(['companyName'])
            ->set('companyName', 'XYZ Bhd')
            ->call('goNext')
            ->assertSet('step', 2)
            ->set('purpose', 'Percutian')
            ->set('pickupState', 'Selangor')
            ->set('pickupLocation', 'Shah Alam')
            ->set('pickupDate', now()->addDay()->toDateString())
            ->set('pickupTime', '10:00')
            ->set('returnDate', now()->addDays(2)->toDateString())
            ->set('vehicleId', 'other')
            ->set('passengers', '2')
            ->call('goNext')
            ->assertHasErrors(['pickupDate' => 'after_or_equal', 'otherVehicleModel' => 'required'])
            ->assertSet('step', 2);
    }

    public function test_invalid_state_phone_and_vehicle_are_rejected(): void
    {
        $component = Livewire::test(RequestForm::class)->call('selectCategory', 'individual')->call('goNext')
            ->set('purpose', 'Percutian')
            ->set('pickupState', 'Sabah')
            ->set('pickupLocation', 'KK')
            ->set('pickupDate', BookingPolicy::earliestPickupDateString())
            ->set('pickupTime', '10:00')
            ->set('returnDate', BookingPolicy::earliestPickupDateString())
            ->set('vehicleId', '999')
            ->set('passengers', '2')
            ->call('goNext')
            ->assertHasErrors(['pickupState', 'vehicleId']);

        $this->assertTrue(RequestForm::isValidPhone('0123456789'));
        $this->assertTrue(RequestForm::isValidPhone('+60 11-1694 6696'));
        $this->assertTrue(RequestForm::isValidPhone('+6591234567'));
        $this->assertFalse(RequestForm::isValidPhone('12345'));
    }

    public function test_honeypot_does_not_create_lead(): void
    {
        Mail::fake();
        Livewire::test(RequestForm::class)->set('website', 'spam')->call('submit')->assertSet('submitted', true);
        $this->assertSame(0, Lead::count());
        Mail::assertNothingSent();
    }

    public function test_category_and_vehicle_prefill_from_query(): void
    {
        $vehicle = $this->vehicle();
        Livewire::withQueryParams(['kategori' => 'event', 'kenderaan' => (string) $vehicle->id])
            ->test(RequestForm::class)
            ->assertSet('customerCategory', 'event')
            ->assertSet('vehicleId', (string) $vehicle->id);
    }
}
