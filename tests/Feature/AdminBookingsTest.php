<?php

namespace Tests\Feature;

use App\Livewire\Admin\BookingsTable;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBookingsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@example.com',
            'password' => bcrypt('secret-password'), 'role' => 'admin',
        ]);
    }

    protected function lead(array $attrs = []): Lead
    {
        return Lead::create(array_merge([
            'source' => 'general', 'customer_category' => 'individual', 'full_name' => 'Ahmad Ujian',
            'phone' => '0123456789', 'email' => 'ahmad@example.com', 'pickup_state' => 'Selangor',
            'pickup_location' => 'Shah Alam', 'arrival_date' => '2026-10-20', 'arrival_time' => '10:00:00',
            'end_date' => '2026-10-22', 'purpose' => 'Percutian', 'vehicle_name_snapshot' => 'Proton X70',
            'passengers' => 4, 'driver_license' => 'malaysia', 'notes' => 'Nota asal',
            'consent' => true, 'status' => 'baru', 'locale' => 'ms', 'submitted_at' => now(),
        ], $attrs));
    }

    public function test_bookings_page_renders_with_actions(): void
    {
        $this->lead();
        $this->actingAs($this->admin())
            ->get('/admin/bookings')
            ->assertOk()
            ->assertSee('SWL-00001')
            ->assertSee('Lihat')->assertSee('Edit')->assertSee('Padam');
    }

    public function test_view_opens_detail_modal(): void
    {
        $lead = $this->lead();
        $this->actingAs($this->admin());

        Livewire::test(BookingsTable::class)
            ->call('viewLead', $lead->id)
            ->assertSet('selectedLeadId', $lead->id)
            ->assertSet('mode', 'view')
            ->assertSee('Detail Pelanggan')
            ->assertSee('Shah Alam')
            ->assertSee('Nota asal')
            ->assertSee('WhatsApp Pelanggan')
            ->call('closeLead')
            ->assertSet('selectedLeadId', null)
            ->assertDontSee('Detail Pelanggan');
    }

    public function test_view_outstation_lead(): void
    {
        $lead = $this->lead(['source' => 'outstation', 'customer_category' => null, 'origin' => 'Johor Bahru', 'airport' => 'KLIA', 'destination' => 'KLCC']);
        $this->actingAs($this->admin());

        Livewire::test(BookingsTable::class)
            ->call('viewLead', $lead->id)
            ->assertSee('Johor Bahru')->assertSee('KLIA')->assertSee('KLCC');
    }

    public function test_edit_and_save_lead(): void
    {
        $lead = $this->lead();
        $this->actingAs($this->admin());

        Livewire::test(BookingsTable::class)
            ->call('editLead', $lead->id)
            ->assertSet('mode', 'edit')
            ->assertSet('form.full_name', 'Ahmad Ujian')
            ->assertSet('form.arrival_date', '2026-10-20')
            ->assertSet('form.arrival_time', '10:00')
            ->set('form.full_name', 'Ahmad Dikemas Kini')
            ->set('form.status', 'dihubungi')
            ->set('form.pickup_location', 'Petaling Jaya')
            ->set('form.end_date', '2026-10-25')
            ->set('form.passengers', '6')
            ->set('form.notes', '')
            ->call('saveLead')
            ->assertHasNoErrors()
            ->assertSet('mode', 'view')
            ->assertSee('berjaya dikemas kini');

        $lead->refresh();
        $this->assertSame('Ahmad Dikemas Kini', $lead->full_name);
        $this->assertSame('dihubungi', $lead->status);
        $this->assertSame('Petaling Jaya', $lead->pickup_location);
        $this->assertSame('2026-10-25', $lead->end_date->toDateString());
        $this->assertSame(6, $lead->passengers);
        $this->assertNull($lead->notes);
    }

    public function test_edit_validation(): void
    {
        $lead = $this->lead();
        $this->actingAs($this->admin());

        Livewire::test(BookingsTable::class)
            ->call('editLead', $lead->id)
            ->set('form.full_name', '')
            ->set('form.email', 'bukan-email')
            ->set('form.end_date', '2026-10-01')
            ->set('form.status', 'xyz')
            ->call('saveLead')
            ->assertHasErrors(['form.full_name', 'form.email', 'form.end_date', 'form.status'])
            ->assertSet('mode', 'edit');

        $this->assertSame('Ahmad Ujian', $lead->fresh()->full_name);
    }

    public function test_delete_lead(): void
    {
        $lead = $this->lead();
        $other = $this->lead(['full_name' => 'Kekal']);
        $this->actingAs($this->admin());

        Livewire::test(BookingsTable::class)
            ->call('viewLead', $lead->id)
            ->call('deleteLead', $lead->id)
            ->assertSet('selectedLeadId', null)
            ->assertSee('telah dipadam');

        $this->assertNull(Lead::find($lead->id));
        $this->assertNotNull(Lead::find($other->id));
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin/bookings')->assertRedirect(route('admin.login'));
    }
}
