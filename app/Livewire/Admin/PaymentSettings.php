<?php

namespace App\Livewire\Admin;

use App\Models\PageSetting;
use App\Support\CarCatalog;
use App\Support\Pricing;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Admin → Harga & Bayaran: bank / DuitNow details, PIC name and pricing formula. */
class PaymentSettings extends Component
{
    use WithFileUploads;

    public string $picName = '';
    public string $bankName = '';
    public string $bankAccountName = '';
    public string $bankAccountNumber = '';
    public $qr;

    public array $pricing = [];
    public array $deposits = [];

    public bool $saved = false;

    public const NUMERIC = [
        'list_markup' => ['Markup harga asal (%)', 'Untuk kereta harga tunggal: harga asal = harga booking + %.'],
        'three_day_discount' => ['Diskaun pakej 3 hari+ (%)', 'Untuk sewaan 3–6 hari.'],
        'weekly_days' => ['Mingguan = harga berapa hari', 'Contoh 6 = bayar 6 hari untuk 7 hari.'],
        'monthly_days' => ['Bulanan = harga berapa hari', 'Contoh 20 = bayar 20 hari untuk 30 hari.'],
        'yearly_months' => ['Tahunan = harga berapa bulan', 'Contoh 10 = bayar 10 bulan untuk 12 bulan.'],
        'extra_hour_divisor' => ['Caj sejam = harga sehari ÷', 'Polisi jam tambahan (GMHUB: ÷ 12).'],
        'quote_hold_hours' => ['Tawaran dipegang (jam)', 'Dipaparkan dalam template WhatsApp.'],
        'deposit_refund_days' => ['Deposit dipulangkan dalam (hari)', 'GMHUB NOTA 109: tidak melebihi 14 hari.'],
    ];

    public function mount(): void
    {
        $s = PageSetting::current();
        $this->picName = (string) $s->pic_name;
        $this->bankName = (string) $s->bank_name;
        $this->bankAccountName = (string) $s->bank_account_name;
        $this->bankAccountNumber = (string) $s->bank_account_number;

        Pricing::flush();
        $settings = Pricing::settings();
        foreach (array_keys(self::NUMERIC) as $key) {
            $this->pricing[$key] = (string) $settings[$key];
        }
        foreach (CarCatalog::CATEGORIES as $key => $c) {
            $this->deposits[$key] = (string) $settings['deposits'][$key];
        }
    }

    public function save(): void
    {
        $rules = [
            'picName' => ['nullable', 'string', 'max:80'],
            'bankName' => ['nullable', 'string', 'max:80'],
            'bankAccountName' => ['nullable', 'string', 'max:120'],
            'bankAccountNumber' => ['nullable', 'string', 'max:40', 'regex:/^[0-9 \-]+$/'],
            'qr' => ['nullable', 'image', 'max:4096'],
        ];
        foreach (array_keys(self::NUMERIC) as $key) {
            $rules['pricing.'.$key] = ['required', 'numeric', 'min:0', 'max:1000'];
        }
        foreach (array_keys(CarCatalog::CATEGORIES) as $key) {
            $rules['deposits.'.$key] = ['required', 'integer', 'min:0', 'max:100000'];
        }
        $this->validate($rules, [
            'required' => 'Wajib diisi.',
            'bankAccountNumber.regex' => 'Nombor akaun hanya digit.',
            'qr.image' => 'Muat naik gambar QR (PNG/JPG).',
        ]);

        $s = PageSetting::current();
        $data = [
            'pic_name' => $this->picName ?: null,
            'bank_name' => $this->bankName ?: null,
            'bank_account_name' => $this->bankAccountName ?: null,
            'bank_account_number' => $this->bankAccountNumber ?: null,
            'pricing' => array_merge(
                array_map(fn ($v) => $v + 0, $this->pricing),
                ['deposits' => array_map('intval', $this->deposits)]
            ),
        ];
        if ($this->qr) {
            if ($s->duitnow_qr_path) {
                Storage::disk('public')->delete($s->duitnow_qr_path);
            }
            $data['duitnow_qr_path'] = $this->qr->store('settings', 'public');
        }
        $s->update($data);
        Pricing::flush();
        $this->reset('qr');
        $this->saved = true;
    }

    public function removeQr(): void
    {
        $s = PageSetting::current();
        if ($s->duitnow_qr_path) {
            Storage::disk('public')->delete($s->duitnow_qr_path);
            $s->update(['duitnow_qr_path' => null]);
        }
    }

    public function render()
    {
        return view('livewire.admin.payment-settings', [
            'settings' => PageSetting::current(),
            'categories' => CarCatalog::CATEGORIES,
            'example' => Pricing::packages(150),
        ])->layout('layouts.admin');
    }
}
