<?php

namespace App\Livewire\Admin;

use App\Mail\BookingCustomerUpdate;
use App\Models\CarRate;
use App\Models\Lead;
use App\Support\BookingSop;
use App\Support\BookingWorkflow;
use App\Support\Pricing;
use App\Support\WhatsAppTemplates;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Admin → Urus Tempahan: one booking from enquiry to deposit refund,
 * with the SOP checklist and compliance status for every phase.
 */
class BookingManager extends Component
{
    #[Locked]
    public int $leadId;

    // Quote
    public string $carRateId = '';
    public string $quotedDaily = '';
    public string $deliveryFee = '';

    // Eligibility decision
    public string $decisionNote = '';

    // Handover
    public string $mileageOut = '';
    public string $fuelOut = 'Penuh';
    public string $usageArea = '';
    public string $mileageLimit = '';
    public string $excessKmRate = '';

    // Return
    public string $mileageIn = '';
    public string $fuelIn = 'Penuh';
    public string $depositDeduction = '';
    public string $deductionReason = '';

    public ?string $flash = null;
    public ?string $flashType = null;

    public const FUEL_LEVELS = ['Penuh', '7/8', '3/4', '5/8', '1/2', '3/8', '1/4', '1/8', 'Kosong'];

    public function mount(Lead $lead): void
    {
        $this->leadId = $lead->id;
        $this->fillFrom($lead);
    }

    protected function fillFrom(Lead $lead): void
    {
        $this->carRateId = (string) ($lead->car_rate_id ?? '');
        $this->quotedDaily = (string) ($lead->quoted_daily ?? '');
        $this->deliveryFee = (string) ($lead->delivery_fee ?? '');
        $this->mileageOut = (string) ($lead->mileage_out ?? '');
        $this->fuelOut = $lead->fuel_out ?: 'Penuh';
        $this->usageArea = (string) ($lead->usage_area ?? ($lead->pickup_state ? 'Semenanjung Malaysia' : ''));
        $this->mileageLimit = (string) ($lead->mileage_limit ?? '');
        $this->excessKmRate = (string) ($lead->excess_km_rate ?? '');
        $this->mileageIn = (string) ($lead->mileage_in ?? '');
        $this->fuelIn = $lead->fuel_in ?: 'Penuh';
        $this->depositDeduction = (string) ($lead->deposit_deduction ?? '');
        $this->deductionReason = (string) ($lead->deposit_deduction_reason ?? '');
    }

    protected function lead(): Lead
    {
        return Lead::with(['carRate', 'documents', 'events.user'])->findOrFail($this->leadId);
    }

    protected function ok(string $msg): void
    {
        $this->flash = $msg;
        $this->flashType = 'success';
    }

    protected function fail(string $msg): void
    {
        $this->flash = $msg;
        $this->flashType = 'error';
    }

    public function dismissFlash(): void
    {
        $this->flash = null;
    }

    /* ---------------- Quote ---------------- */

    public function updatedCarRateId(): void
    {
        $rate = ctype_digit($this->carRateId) ? CarRate::find($this->carRateId) : null;
        $this->quotedDaily = $rate ? (string) $rate->bookingDaily() : '';
    }

    public function saveQuote(): void
    {
        $this->validate([
            'carRateId' => ['required', 'integer', 'exists:car_rates,id'],
            'quotedDaily' => ['required', 'integer', 'min:1', 'max:100000'],
            'deliveryFee' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'carRateId.required' => 'Pilih kereta daripada senarai harga.',
            'quotedDaily.required' => 'Masukkan harga sehari.',
        ]);

        $lead = $this->lead();
        $rate = CarRate::findOrFail($this->carRateId);
        $lead->applyQuote($rate, (int) $this->quotedDaily);
        $lead->delivery_fee = $this->deliveryFee !== '' ? (int) $this->deliveryFee : null;
        $lead->vehicle_name_snapshot = $rate->name();
        $lead->save();
        $lead->logEvent('tawaran_dikira', 'Tawaran dikira: '.$rate->name().' '.Pricing::rm($lead->quoted_daily).'/hari, jumlah '.Pricing::rm($lead->amountDue()));
        $this->ok('Tawaran harga disimpan. Jumlah perlu dibayar: '.Pricing::rm($lead->amountDue()).'.');
    }

    /** Called by the WhatsApp template links (the link itself opens WhatsApp). */
    public function markTemplate(string $template): void
    {
        if (! array_key_exists($template, WhatsAppTemplates::LABELS)) {
            return;
        }
        $lead = $this->lead();
        BookingWorkflow::recordTemplateSent($lead, $template);
        $this->ok('Direkod: '.WhatsAppTemplates::LABELS[$template].'.');
    }

    /* ---------------- SOP checklist ---------------- */

    public function toggleCheck(string $key): void
    {
        $known = collect(BookingSop::PHASES)->flatMap(fn ($p) => array_keys($p[2]))->all();
        if (! in_array($key, $known, true)) {
            return;
        }
        $lead = $this->lead();
        if ($lead->isChecked($key)) {
            $lead->uncheck($key)->save();
            $lead->logEvent('sop_batal', 'SOP dibatalkan: '.BookingSop::itemLabel($key));
        } else {
            $lead->check($key)->save();
            $lead->logEvent('sop', 'SOP selesai: '.BookingSop::itemLabel($key));
        }
    }

    protected function missing(Lead $lead, array $keys): array
    {
        return array_values(array_filter($keys, fn ($k) => ! $lead->isChecked($k)));
    }

    protected function failMissing(array $missing, string $action): void
    {
        $this->fail('Belum boleh '.$action.'. Lengkapkan SOP: '.implode(' · ', array_map([BookingSop::class, 'itemLabel'], $missing)).'.');
    }

    /* ---------------- Eligibility ---------------- */

    public function approveVerification(): void
    {
        $lead = $this->lead();
        if ($lead->stage !== 'semakan') {
            $this->fail('Kelayakan hanya boleh diluluskan selepas pelanggan menghantar dokumen.');
            return;
        }
        if (! $lead->hasQuote()) {
            $this->fail('Simpan tawaran harga dahulu supaya jumlah bayaran boleh dipaparkan kepada pelanggan.');
            return;
        }
        $missing = $this->missing($lead, ['semak_ic_lesen', 'sahkan_telefon', 'semak_alamat', 'semak_kerja', 'semak_saringan', 'semak_waris']);
        if ($missing) {
            $this->failMissing($missing, 'luluskan kelayakan');
            return;
        }

        $lead->verification_approved_at = now();
        $lead->verification_note = filled($this->decisionNote) ? $this->decisionNote : null;
        $lead->check('kelayakan_lulus')->check('jumlah_dimaklumkan');
        $lead->moveTo('bayaran')->save();
        $lead->logEvent('kelayakan_lulus', 'Kelayakan diluluskan'.($lead->verification_note ? ': '.$lead->verification_note : ''));

        $this->mailCustomer($lead, 'approved');
        $this->decisionNote = '';
        $this->ok('Kelayakan diluluskan. E-mel pautan bayaran dihantar kepada pelanggan — hantar juga WhatsApp "Kelayakan lulus & arahan bayaran".');
    }

    public function rejectVerification(): void
    {
        $this->validate(['decisionNote' => ['required', 'string', 'max:500']], ['decisionNote.required' => 'Nyatakan sebab (rekod dalaman).']);
        $lead = $this->lead();
        $lead->verification_note = $this->decisionNote;
        $lead->moveTo('ditolak')->save();
        $lead->logEvent('kelayakan_ditolak', 'Kelayakan tidak lulus: '.$this->decisionNote);
        $this->decisionNote = '';
        $this->ok('Permohonan ditanda tidak lulus. Hantar WhatsApp "Kelayakan tidak lulus" kepada pelanggan.');
    }

    public function requestNewDocuments(): void
    {
        $this->validate(['decisionNote' => ['required', 'string', 'max:500']], ['decisionNote.required' => 'Nyatakan dokumen yang perlu dihantar semula.']);
        $lead = $this->lead();
        $lead->verification_submitted_at = null;
        $lead->uncheck('dokumen_diterima');
        $lead->moveTo('kelayakan')->save();
        $lead->logEvent('minta_dokumen', 'Pelanggan diminta menghantar semula: '.$this->decisionNote);
        $this->decisionNote = '';
        $this->ok('Borang kelayakan dibuka semula. Maklumkan pelanggan melalui WhatsApp dengan pautan yang sama.');
    }

    /* ---------------- Payment ---------------- */

    public function verifyPayment(): void
    {
        $lead = $this->lead();
        if ($lead->stage !== 'bayaran') {
            $this->fail('Bayaran hanya boleh disahkan selepas kelayakan diluluskan.');
            return;
        }
        $lead->payment_verified_at = now();
        $lead->payment_amount ??= $lead->amountDue();
        $lead->check('bayaran_disahkan');
        if (! $lead->payment_submitted_at) {
            $lead->payment_submitted_at = now();
        }
        $lead->moveTo('disahkan')->save();
        $lead->logEvent('bayaran_disahkan', 'Bayaran '.Pricing::rm($lead->payment_amount).' disahkan — tempahan DISAHKAN');

        $this->mailCustomer($lead, 'confirmed');
        $this->ok('Bayaran disahkan & tempahan DISAHKAN. E-mel pengesahan dihantar — hantar juga WhatsApp "Tempahan disahkan".');
    }

    /* ---------------- Handover ---------------- */

    public function recordHandover(): void
    {
        $lead = $this->lead();
        if ($lead->stage !== 'disahkan') {
            $this->fail('Serahan hanya boleh direkod untuk tempahan yang telah disahkan (bayaran diterima).');
            return;
        }
        $this->validate([
            'mileageOut' => ['required', 'integer', 'min:0', 'max:2000000'],
            'fuelOut' => ['required', 'in:'.implode(',', self::FUEL_LEVELS)],
            'usageArea' => ['required', 'string', 'max:150'],
            'mileageLimit' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'excessKmRate' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ], ['required' => 'Wajib diisi.']);

        $missing = $this->missing($lead, ['kenderaan_sedia', 'minyak_penuh', 'dokumen_asal', 'video_serahan', 'terangkan_terma', 'vua_ditandatangani']);
        if ($missing) {
            $this->failMissing($missing, 'serah kereta');
            return;
        }

        $lead->fill([
            'handover_at' => now(),
            'mileage_out' => (int) $this->mileageOut,
            'fuel_out' => $this->fuelOut,
            'usage_area' => $this->usageArea,
            'mileage_limit' => $this->mileageLimit !== '' ? (int) $this->mileageLimit : null,
            'excess_km_rate' => $this->excessKmRate !== '' ? (int) $this->excessKmRate : null,
        ]);
        $lead->moveTo('diserah')->save();
        $lead->logEvent('diserah', 'Kereta diserah — odometer '.number_format($lead->mileage_out).' km, minyak '.$lead->fuel_out);
        $this->ok('Serahan kereta direkod.');
    }

    /* ---------------- Return & deposit ---------------- */

    public function recordReturn(): void
    {
        $lead = $this->lead();
        if ($lead->stage !== 'diserah') {
            $this->fail('Pulangan hanya boleh direkod selepas kereta diserah.');
            return;
        }
        $this->validate([
            'mileageIn' => ['required', 'integer', 'min:'.(int) $lead->mileage_out, 'max:2000000'],
            'fuelIn' => ['required', 'in:'.implode(',', self::FUEL_LEVELS)],
        ], ['required' => 'Wajib diisi.', 'mileageIn.min' => 'Odometer pulangan tidak boleh kurang daripada semasa serahan.']);

        $missing = $this->missing($lead, ['video_pulangan', 'semak_minyak_mileage']);
        if ($missing) {
            $this->failMissing($missing, 'rekod pulangan');
            return;
        }

        $lead->fill(['returned_at' => now(), 'mileage_in' => (int) $this->mileageIn, 'fuel_in' => $this->fuelIn]);
        $lead->moveTo('dipulang')->save();
        $used = $lead->mileage_in - (int) $lead->mileage_out;
        $lead->logEvent('dipulang', 'Kereta dipulang — '.number_format($used).' km digunakan, minyak '.$lead->fuel_in);
        $this->ok('Pulangan direkod. Deposit perlu dipulangkan sebelum '.$lead->depositRefundDue()->format('d/m/Y').'.');
    }

    public function refundDeposit(): void
    {
        $lead = $this->lead();
        if ($lead->stage !== 'dipulang') {
            $this->fail('Deposit dipulangkan selepas kereta dipulang dan diperiksa.');
            return;
        }
        $this->validate([
            'depositDeduction' => ['nullable', 'integer', 'min:0', 'max:'.(int) $lead->deposit_amount],
            'deductionReason' => [$this->depositDeduction !== '' && (int) $this->depositDeduction > 0 ? 'required' : 'nullable', 'string', 'max:250'],
        ], ['deductionReason.required' => 'Nyatakan sebab potongan (perlu ada bukti).', 'depositDeduction.max' => 'Potongan melebihi deposit.']);

        $lead->deposit_deduction = $this->depositDeduction !== '' ? (int) $this->depositDeduction : null;
        $lead->deposit_deduction_reason = filled($this->deductionReason) ? $this->deductionReason : null;
        $lead->deposit_refunded_at = now();
        $lead->check('deposit_dipulangkan');
        $lead->moveTo('selesai')->save();
        $refund = (int) $lead->deposit_amount - (int) $lead->deposit_deduction;
        $lead->logEvent('deposit', 'Deposit '.Pricing::rm($refund).' dipulangkan'.($lead->deposit_deduction ? ' (potongan '.Pricing::rm($lead->deposit_deduction).': '.$lead->deposit_deduction_reason.')' : ''));
        $this->ok('Deposit direkod sebagai dipulangkan. Tempahan SELESAI — hantar WhatsApp "Terima kasih".');
    }

    public function cancelBooking(): void
    {
        $lead = $this->lead();
        $lead->moveTo('batal')->save();
        $lead->logEvent('batal', 'Tempahan dibatalkan'.(filled($this->decisionNote) ? ': '.$this->decisionNote : ''));
        $this->decisionNote = '';
        $this->ok('Tempahan dibatalkan.');
    }

    public function reopenBooking(): void
    {
        $lead = $this->lead();
        $target = match (true) {
            (bool) $lead->payment_verified_at => 'disahkan',
            (bool) $lead->verification_approved_at => 'bayaran',
            (bool) $lead->verification_submitted_at => 'semakan',
            (bool) $lead->verification_link_sent_at => 'kelayakan',
            (bool) $lead->quote_sent_at => 'tawaran',
            default => 'permohonan',
        };
        $lead->moveTo($target)->save();
        $lead->logEvent('dibuka_semula', 'Tempahan dibuka semula ke peringkat: '.BookingSop::stageLabel($target));
        $this->ok('Tempahan dibuka semula.');
    }

    protected function mailCustomer(Lead $lead, string $type): void
    {
        if (blank($lead->email)) {
            return;
        }
        try {
            Mail::to($lead->email)->send(new BookingCustomerUpdate($lead, $type));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function render()
    {
        $lead = $this->lead();
        $templates = [];
        foreach (array_keys(WhatsAppTemplates::LABELS) as $t) {
            $templates[$t] = WhatsAppTemplates::url($lead, $t);
        }

        $selectedRate = ctype_digit($this->carRateId) ? CarRate::find($this->carRateId) : null;
        $preview = null;
        if ($selectedRate && ctype_digit($this->quotedDaily) && (int) $this->quotedDaily > 0) {
            $days = Pricing::days($lead->arrival_date, $lead->end_date);
            $calc = Pricing::rentalTotal((int) $this->quotedDaily, $days);
            $deposit = $selectedRate->depositAmount($days);
            $preview = [
                'days' => $days,
                'list' => max($selectedRate->listDaily(), (int) $this->quotedDaily),
                'total' => $calc['total'],
                'package' => Pricing::PACKAGE_LABELS[$calc['package']] ?? $calc['package'],
                'deposit' => $deposit,
                'due' => $calc['total'] + $deposit + (int) ($this->deliveryFee ?: 0),
                'agent' => $selectedRate->agent_rate,
                'vip' => $selectedRate->vip_rate,
                'margin' => $selectedRate->agent_rate ? ((int) $this->quotedDaily - $selectedRate->agent_rate) * $days : null,
            ];
        }

        return view('livewire.admin.booking-manager', [
            'lead' => $lead,
            'compliance' => BookingSop::compliance($lead),
            'rates' => CarRate::orderBy('brand')->orderBy('sort_order')->get()->groupBy('brand'),
            'templates' => $templates,
            'preview' => $preview,
            'stageIndex' => BookingSop::stageIndex($lead->stage),
        ])->layout('layouts.admin');
    }
}
