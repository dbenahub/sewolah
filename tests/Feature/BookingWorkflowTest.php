<?php

namespace Tests\Feature;

use App\Http\Controllers\PicActionController;
use App\Livewire\Admin\BookingManager;
use App\Livewire\Admin\CarRates;
use App\Livewire\Admin\PaymentSettings;
use App\Livewire\Public\BookingPortal;
use App\Mail\BookingAlert;
use App\Mail\BookingCustomerUpdate;
use App\Mail\NewLeadNotification;
use App\Models\CarRate;
use App\Models\Lead;
use App\Models\PageSetting;
use App\Models\User;
use App\Support\BookingSop;
use App\Support\Pricing;
use App\Support\WhatsAppTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Pricing::flush();
    }

    protected function admin(): User
    {
        return User::create(['name' => 'Nizam', 'email' => 'nizam@example.com', 'password' => bcrypt('secret-password'), 'role' => 'admin']);
    }

    protected function rate(string $model): CarRate
    {
        return CarRate::where('model', $model)->firstOrFail();
    }

    protected function lead(array $attrs = [], string $model = 'Alphard SC'): Lead
    {
        $lead = Lead::create(array_merge([
            'source' => 'general', 'customer_category' => 'individual', 'full_name' => 'Ahmad Ujian',
            'phone' => '012-345 6789', 'email' => 'ahmad@example.com', 'pickup_state' => 'Selangor',
            'pickup_location' => 'KLIA', 'arrival_date' => now()->addDays(10)->toDateString(), 'arrival_time' => '10:00:00',
            'end_date' => now()->addDays(13)->toDateString(), 'purpose' => 'Percutian', 'vehicle_name_snapshot' => 'Toyota Alphard SC',
            'passengers' => 4, 'driver_license' => 'malaysia', 'consent' => true, 'status' => 'baru', 'locale' => 'ms', 'submitted_at' => now(),
        ], $attrs));
        $lead->applyQuote($this->rate($model))->save();

        return $lead->fresh();
    }

    /* ------------------------------------------------------------ pricing */

    public function test_price_list_is_seeded_with_retail_agent_and_vip_prices(): void
    {
        $this->assertGreaterThanOrEqual(60, CarRate::count());

        $bezza = $this->rate('Bezza');
        $this->assertSame([120, 100, 150], [$bezza->agent_rate, $bezza->vip_rate, $bezza->retail_min]);
        $this->assertSame(180, $bezza->listDaily());          // single price: +20%, rounded to RM10
        $this->assertSame(150, $bezza->bookingDaily());
        $this->assertSame(200, $bezza->depositAmount());      // ekonomi
        $this->assertSame(['daily' => 150, 'three_day' => 428, 'weekly' => 900, 'monthly' => 3000, 'yearly' => 30000], $bezza->packages());

        $alphard = $this->rate('Alphard SC');
        $this->assertSame(1300, $alphard->listDaily());       // range: highest = harga asal
        $this->assertSame(1000, $alphard->bookingDaily());    // lowest = harga booking
        $this->assertSame(1000, $alphard->depositAmount());   // premium
        $this->assertSame(20000, $alphard->depositAmount(30)); // monthly rental: deposit = 1 month

        $this->assertSame(3000, $this->rate('Cayman 718 (GT4 Bodykit)')->depositAmount());
    }

    public function test_rental_total_picks_the_cheapest_package(): void
    {
        $this->assertSame(['total' => 300, 'package' => 'harian', 'effective_daily' => 150], Pricing::rentalTotal(150, 2));
        $this->assertSame('3hari', Pricing::rentalTotal(150, 4)['package']);
        $this->assertSame(570, Pricing::rentalTotal(150, 4)['total']);
        $this->assertSame(900, Pricing::rentalTotal(150, 7)['total']);
        $this->assertSame(3000, Pricing::rentalTotal(150, 25)['total']);  // capped at monthly
        $this->assertSame(3000, Pricing::rentalTotal(150, 30)['total']);
        $this->assertSame(30000, Pricing::rentalTotal(150, 365)['total']);
    }

    /* ------------------------------------------------------------ PIC email & WhatsApp */

    public function test_pic_email_has_quote_and_one_tap_whatsapp_actions(): void
    {
        $lead = $this->lead();
        $html = (new NewLeadNotification($lead))->render();

        $this->assertStringContainsString('RM1,300', $html);
        $this->assertStringContainsString('RM1,000/hari', $html);
        $this->assertStringContainsString('SAHKAN AVAILABLE &amp; HARGA', $html);
        $this->assertStringContainsString('HANTAR BORANG KELAYAKAN', $html);
        $this->assertStringContainsString('/pic/tempahan/'.$lead->id.'/quote', $html);
        $this->assertStringContainsString('signature=', $html);
    }

    public function test_signed_quote_link_records_sop_and_opens_whatsapp_template(): void
    {
        $lead = $this->lead();

        $this->get('/pic/tempahan/'.$lead->id.'/quote')->assertForbidden();   // unsigned

        $response = $this->get(PicActionController::signedUrl($lead, 'quote'));
        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/60123456789?text=', $target);
        $text = rawurldecode($target);
        $this->assertStringContainsString('AVAILABLE', $text);
        $this->assertStringContainsString('~RM1,300/hari~', $text);
        $this->assertStringContainsString('*RM1,000/hari*', $text);
        $this->assertStringContainsString('JUMLAH BAYARAN: RM3,850', $text);
        $this->assertStringContainsString('Mingguan (7 hari): RM6,000', $text);

        $lead->refresh();
        $this->assertSame('tawaran', $lead->stage);
        $this->assertSame('quotation_dihantar', $lead->status);
        $this->assertTrue($lead->isChecked('hantar_tawaran'));
        $this->assertNotNull($lead->quote_sent_at);

        $response = $this->get(PicActionController::signedUrl($lead, 'verification'));
        $lead->refresh();
        $this->assertSame('kelayakan', $lead->stage);
        $this->assertNotNull($lead->access_token);
        $this->assertStringContainsString(rawurlencode($lead->portalUrl()), $response->headers->get('Location'));
        $this->assertTrue($lead->isChecked('pelanggan_setuju'));
    }

    public function test_all_whatsapp_templates_render(): void
    {
        PageSetting::current()->update(['bank_name' => 'Maybank', 'bank_account_number' => '5123 4567 8901', 'pic_name' => 'Nizam']);
        $lead = $this->lead();
        foreach (array_keys(WhatsAppTemplates::LABELS) as $t) {
            $text = WhatsAppTemplates::text($lead, $t);
            $this->assertNotSame('', $text, $t);
            $this->assertStringContainsString('Ahmad Ujian', $text, $t);
        }
        $this->assertStringContainsString('Maybank', WhatsAppTemplates::text($lead, 'approved'));
        $this->assertStringContainsString('Nizam', WhatsAppTemplates::text($lead, 'handover'));

        $noCar = Lead::create(['full_name' => 'Tiada Kereta', 'phone' => '0111111111', 'status' => 'baru', 'vehicle_name_snapshot' => 'Tiada pilihan khusus']);
        $this->assertStringContainsString('model kereta pilihan', WhatsAppTemplates::text($noCar, 'quote'));
    }

    /* ------------------------------------------------------------ customer portal */

    protected function verificationPayload(): array
    {
        return [
            'icNumber' => '900101-14-5678', 'dateOfBirth' => '1990-01-01', 'homeAddress' => 'No 1, Jalan Ujian 1, 40000 Shah Alam, Selangor',
            'occupation' => 'Jurutera', 'employer' => 'ABC Sdn Bhd', 'licenseNumber' => 'D1234567',
            'licenseExpiry' => now()->addYears(2)->toDateString(), 'emergencyName' => 'Siti', 'emergencyRelation' => 'Isteri',
            'emergencyPhone' => '0198765432', 'signature' => 'Ahmad Ujian',
            'agreeTerms' => true, 'agreeFuel' => true, 'agreeDeposit' => true, 'agreeCancel' => true, 'agreeUsage' => true,
        ];
    }

    public function test_portal_requires_valid_token_and_is_private(): void
    {
        $this->get('/tempahan/'.str_repeat('x', 48))->assertNotFound();

        $lead = $this->lead();
        $this->get($lead->portalUrl())
            ->assertOk()
            ->assertSee('Semakan kelayakan penyewa')
            ->assertSee('RM1,000')
            ->assertSee('noindex', false)
            ->assertDontSee('fbq(', false);
    }

    public function test_customer_submits_eligibility_documents_and_terms(): void
    {
        Storage::fake('local');
        Mail::fake();
        PageSetting::current()->update(['admin_notification_email' => 'sewolah.hq@gmail.com']);
        $lead = $this->lead();
        $lead->ensureAccessToken();

        // Missing documents & agreements are rejected
        Livewire::test(BookingPortal::class, ['token' => $lead->access_token])
            ->call('submitVerification')
            ->assertHasErrors(['icNumber', 'icFront', 'license', 'bill', 'agreeTerms', 'agreeFuel', 'signature']);

        $component = Livewire::test(BookingPortal::class, ['token' => $lead->access_token]);
        foreach ($this->verificationPayload() as $k => $v) {
            $component->set($k, $v);
        }
        $component
            ->set('icFront', UploadedFile::fake()->image('ic-depan.jpg', 1200, 800))
            ->set('icBack', UploadedFile::fake()->image('ic-belakang.jpg'))
            ->set('license', UploadedFile::fake()->image('lesen.png'))
            ->set('bill', UploadedFile::fake()->create('bil-tnb.pdf', 300, 'application/pdf'))
            ->call('submitVerification')
            ->assertHasNoErrors()
            ->assertSet('justSubmitted', true);

        $lead->refresh();
        $this->assertSame('semakan', $lead->stage);
        $this->assertSame('900101-14-5678', $lead->ic_number);
        $this->assertSame('Ahmad Ujian', $lead->terms_signature);
        $this->assertNotNull($lead->terms_accepted_at);
        $this->assertCount(4, $lead->documents);
        foreach ($lead->documents as $doc) {
            Storage::disk('local')->assertExists($doc->path);
            $this->assertStringStartsWith('bookings/'.$lead->id.'/', $doc->path);
        }
        $this->assertTrue($lead->isChecked('dokumen_diterima'));
        $this->assertTrue($lead->isChecked('terma_diterima'));
        Mail::assertSent(BookingAlert::class, fn ($m) => $m->hasTo('sewolah.hq@gmail.com') && $m->type === 'documents');

        // Documents are not public: guests cannot open them, admins can
        $doc = $lead->documents->first();
        $this->get(route('admin.bookings.document', [$lead, $doc]))->assertRedirect();
        $this->actingAs($this->admin())->get(route('admin.bookings.document', [$lead, $doc]))->assertOk();
        $other = $this->lead(['full_name' => 'Orang Lain']);
        $this->get(route('admin.bookings.document', [$other, $doc]))->assertNotFound();
    }

    public function test_rejects_invalid_files_and_expired_licence(): void
    {
        Storage::fake('local');
        $lead = $this->lead();
        $lead->ensureAccessToken();
        $component = Livewire::test(BookingPortal::class, ['token' => $lead->access_token]);
        foreach ($this->verificationPayload() as $k => $v) {
            $component->set($k, $v);
        }
        $component
            ->set('licenseExpiry', now()->addDay()->toDateString())   // expires before return date
            ->set('icFront', UploadedFile::fake()->create('virus.exe', 10))
            ->set('license', UploadedFile::fake()->image('lesen.png'))
            ->set('bill', UploadedFile::fake()->create('bil.pdf', 20000, 'application/pdf')) // 20MB
            ->call('submitVerification')
            ->assertHasErrors(['licenseExpiry', 'icFront', 'bill']);
        $this->assertSame(0, $lead->documents()->count());
    }

    /* ------------------------------------------------------------ full admin workflow */

    public function test_full_workflow_from_quote_to_deposit_refund_with_sop_enforcement(): void
    {
        Storage::fake('local');
        Mail::fake();
        $admin = $this->admin();
        $lead = $this->lead();
        $this->actingAs($admin);

        // Admin page renders with phases, price and templates
        $this->get(route('admin.bookings.manage', $lead))
            ->assertOk()->assertSee('Tempahan &amp; Tawaran Harga', false)->assertSee('Template WhatsApp')
            ->assertSee('DALAMAN')->assertSee('RM800 / RM700');

        $mgr = Livewire::test(BookingManager::class, ['lead' => $lead]);
        $mgr->call('markTemplate', 'quote')->call('markTemplate', 'verification');
        $this->assertSame('kelayakan', $lead->fresh()->stage);

        // Customer submits documents through the portal
        $portal = Livewire::test(BookingPortal::class, ['token' => $lead->fresh()->access_token]);
        foreach ($this->verificationPayload() as $k => $v) {
            $portal->set($k, $v);
        }
        $portal->set('icFront', UploadedFile::fake()->image('ic.jpg'))
            ->set('license', UploadedFile::fake()->image('lesen.jpg'))
            ->set('bill', UploadedFile::fake()->image('bil.jpg'))
            ->call('submitVerification')->assertHasNoErrors();

        // Approval is blocked until the eligibility SOP is completed
        $mgr->call('approveVerification')->assertSet('flashType', 'error');
        $this->assertSame('semakan', $lead->fresh()->stage);
        foreach (['semak_ic_lesen', 'sahkan_telefon', 'semak_alamat', 'semak_kerja', 'semak_saringan', 'semak_waris'] as $k) {
            $mgr->call('toggleCheck', $k);
        }
        $mgr->set('decisionNote', 'PSS bersih')->call('approveVerification')->assertSet('flashType', 'success');
        $lead->refresh();
        $this->assertSame('bayaran', $lead->stage);
        Mail::assertSent(BookingCustomerUpdate::class, fn ($m) => $m->type === 'approved' && $m->hasTo('ahmad@example.com'));

        // Customer pays and uploads the receipt
        PageSetting::current()->update(['bank_name' => 'Maybank', 'bank_account_number' => '512345678901', 'bank_account_name' => 'DBENA SDN BHD']);
        $this->get($lead->portalUrl())->assertOk()->assertSee('Maybank')->assertSee('512345678901')->assertSee('RM3,850');
        Livewire::test(BookingPortal::class, ['token' => $lead->access_token])
            ->set('receipt', UploadedFile::fake()->image('resit.jpg'))
            ->set('paymentReference', 'DN123456')
            ->call('submitPayment')->assertHasNoErrors();
        $lead->refresh();
        $this->assertSame(3850, $lead->payment_amount);
        $this->assertTrue($lead->isChecked('resit_diterima'));
        Mail::assertSent(BookingAlert::class, fn ($m) => $m->type === 'payment');

        $mgr->call('verifyPayment');
        $lead->refresh();
        $this->assertSame('disahkan', $lead->stage);
        $this->assertSame('disahkan', $lead->status);
        Mail::assertSent(BookingCustomerUpdate::class, fn ($m) => $m->type === 'confirmed');
        $this->get($lead->portalUrl())->assertSee('Tempahan disahkan');

        // Handover blocked until the handover SOP is done
        $mgr->set('mileageOut', '45000')->set('fuelOut', 'Penuh')->set('usageArea', 'Lembah Klang')
            ->set('mileageLimit', '900')->set('excessKmRate', '1')
            ->call('recordHandover')->assertSet('flashType', 'error');
        foreach (['kenderaan_sedia', 'minyak_penuh', 'dokumen_asal', 'video_serahan', 'terangkan_terma', 'vua_ditandatangani'] as $k) {
            $mgr->call('toggleCheck', $k);
        }
        $mgr->call('recordHandover')->assertSet('flashType', 'success');
        $this->assertSame('diserah', $lead->fresh()->stage);

        // Return: odometer cannot go backwards
        $mgr->set('mileageIn', '44000')->call('recordReturn')->assertHasErrors(['mileageIn']);
        $mgr->call('toggleCheck', 'video_pulangan')->call('toggleCheck', 'semak_minyak_mileage')
            ->set('mileageIn', '45620')->set('fuelIn', '3/4')->call('recordReturn')->assertHasNoErrors();
        $lead->refresh();
        $this->assertSame('dipulang', $lead->stage);
        $this->assertSame(now()->addDays(14)->toDateString(), $lead->depositRefundDue()->toDateString());

        // Deposit: deduction needs a reason and cannot exceed the deposit
        $mgr->set('depositDeduction', '5000')->call('refundDeposit')->assertHasErrors(['depositDeduction']);
        $mgr->set('depositDeduction', '60')->set('deductionReason', '')->call('refundDeposit')->assertHasErrors(['deductionReason']);
        $mgr->set('deductionReason', 'Kurang minyak 2 bar')->call('refundDeposit')->assertHasNoErrors();
        $lead->refresh();
        $this->assertSame('selesai', $lead->stage);
        $this->assertStringContainsString('RM940', WhatsAppTemplates::text($lead, 'thanks'));

        // Every SOP step is logged
        $this->assertGreaterThanOrEqual(15, $lead->events()->count());
        $compliance = BookingSop::compliance($lead);
        // Items nobody ticked (e.g. invoice sent, photo with customer) keep the score honest
        $this->assertSame((int) round($compliance['done'] / $compliance['total'] * 100), $compliance['percent']);
        $this->assertGreaterThanOrEqual(60, $compliance['percent']);
        $this->assertSame(100, $compliance['phases']['kelayakan']['percent']);

        // Bookings list shows the stage and SOP score
        $this->get('/admin/bookings')->assertOk()->assertSee('Selesai')->assertSee('SOP '.$compliance['percent'].'%')->assertSee('Urus');
    }

    public function test_reject_cancel_and_reopen(): void
    {
        $this->actingAs($this->admin());
        $lead = $this->lead(['stage' => 'semakan', 'verification_submitted_at' => now()]);
        Livewire::test(BookingManager::class, ['lead' => $lead])
            ->call('rejectVerification')->assertHasErrors(['decisionNote'])
            ->set('decisionNote', 'Rekod saringan tidak bersih')->call('rejectVerification');
        $this->assertSame('ditolak', $lead->fresh()->stage);
        $this->assertSame('batal', $lead->fresh()->status);
        $this->get($lead->fresh()->portalUrl())->assertSee('tidak dapat diteruskan');

        Livewire::test(BookingManager::class, ['lead' => $lead->fresh()])->call('reopenBooking');
        $this->assertSame('semakan', $lead->fresh()->stage);

        Livewire::test(BookingManager::class, ['lead' => $lead->fresh()])
            ->set('decisionNote', 'IC kabur')->call('requestNewDocuments');
        $this->assertSame('kelayakan', $lead->fresh()->stage);

        Livewire::test(BookingManager::class, ['lead' => $lead->fresh()])->call('cancelBooking');
        $this->assertSame('batal', $lead->fresh()->stage);
    }

    public function test_admin_can_change_quote_and_override_price(): void
    {
        $this->actingAs($this->admin());
        $lead = $this->lead([], 'Bezza');
        Livewire::test(BookingManager::class, ['lead' => $lead])
            ->set('carRateId', (string) $this->rate('Myvi Gen 3')->id)
            ->assertSet('quotedDaily', '150')
            ->set('quotedDaily', '140')
            ->set('deliveryFee', '50')
            ->call('saveQuote')->assertHasNoErrors();
        $lead->refresh();
        $this->assertSame('Perodua Myvi Gen 3', $lead->vehicle_name_snapshot);
        $this->assertSame(140, $lead->quoted_daily);
        $this->assertSame(399, $lead->quoted_rental_total);   // 3 days × 140 − 5%
        $this->assertSame(399 + 200 + 50, $lead->amountDue());
    }

    /* ------------------------------------------------------------ admin settings pages */

    public function test_admin_manages_price_list(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/harga-kereta')->assertOk()->assertSee('Alphard SC')->assertSee('RM1,300')->assertSee('Harga Kereta');

        Livewire::test(CarRates::class)
            ->call('create')
            ->set('brand', 'Toyota')->set('model', 'Corolla Cross')->set('category', 'suv')
            ->set('agentRate', '250')->set('vipRate', '220')->set('retailMin', '330')->set('retailMax', '300')
            ->call('save')->assertHasErrors(['retailMax'])
            ->set('retailMax', '380')->call('save')->assertHasNoErrors();
        $r = CarRate::where('model', 'Corolla Cross')->firstOrFail();
        $this->assertSame(380, $r->listDaily());
        $this->assertSame(330, $r->bookingDaily());
        $this->assertSame(500, $r->depositAmount());

        Livewire::test(CarRates::class)->call('toggleActive', $r->id);
        $this->assertFalse($r->fresh()->is_active);
        $this->get('/form')->assertDontSee('Corolla Cross');
    }

    public function test_admin_saves_bank_qr_and_pricing_formula(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $this->get('/admin/tetapan-bayaran')->assertOk()->assertSee('Formula harga pakej');

        Livewire::test(PaymentSettings::class)
            ->set('bankName', 'Maybank')->set('bankAccountNumber', '5123-4567-8901')->set('bankAccountName', 'DBENA SDN BHD')
            ->set('picName', 'Nizam')
            ->set('qr', UploadedFile::fake()->image('qr.png', 400, 400))
            ->set('pricing.monthly_days', '18')
            ->set('deposits.ekonomi', '250')
            ->call('save')->assertHasNoErrors()->assertSet('saved', true);

        $s = PageSetting::current()->fresh();
        $this->assertSame('Maybank', $s->bank_name);
        Storage::disk('public')->assertExists($s->duitnow_qr_path);
        Pricing::flush();
        $this->assertSame(2700, $this->rate('Bezza')->packages()['monthly']);
        $this->assertSame(250, $this->rate('Bezza')->depositAmount());
    }

    public function test_rental_terms_page_and_form_lists_price_list_cars(): void
    {
        $this->get('/terma-sewaan')->assertOk()
            ->assertSee('Full to Full')->assertSee('72 jam')->assertSee('CTOS')->assertSee('14 hari');
        $form = Livewire::test(\App\Livewire\Public\RequestForm::class)
            ->call('selectCategory', 'individual')->call('goNext')->assertSet('step', 2)
            ->assertSee('Perodua')->assertSee('Alphard SC')->assertSeeHtml('value="r'.$this->rate('Alphard SC')->id.'"')
            ->assertDontSee('RM800'); // agent prices are never shown publicly
    }
}
