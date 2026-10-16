<?php

namespace App\Livewire\Public;

use App\Mail\BookingAlert;
use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\PageSetting;
use App\Support\BookingSop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Customer booking portal (sewolah.com/tempahan/{token}).
 * Shows the quotation and status, collects the eligibility documents,
 * the T&C acceptance and the payment receipt.
 */
class BookingPortal extends Component
{
    use WithFileUploads;

    public const FILE_RULE = ['file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf', 'max:10240'];

    #[Locked]
    public string $token = '';

    // Eligibility
    public string $fullName = '';
    public string $icNumber = '';
    public string $dateOfBirth = '';
    public string $phone = '';
    public string $email = '';
    public string $homeAddress = '';
    public string $occupation = '';
    public string $employer = '';
    public string $licenseNumber = '';
    public string $licenseExpiry = '';
    public string $emergencyName = '';
    public string $emergencyRelation = '';
    public string $emergencyPhone = '';
    public string $socialMedia = '';

    public $icFront;
    public $icBack;
    public $license;
    public $bill;
    public $otherDoc;

    public bool $agreeTerms = false;
    public bool $agreeFuel = false;
    public bool $agreeDeposit = false;
    public bool $agreeCancel = false;
    public bool $agreeUsage = false;
    public string $signature = '';

    // Payment
    public $receipt;
    public string $paymentReference = '';

    public bool $justSubmitted = false;

    public function mount(string $token): void
    {
        $lead = Lead::where('access_token', $token)->first();
        abort_unless($lead, 404);

        $this->token = $token;
        $this->fullName = (string) $lead->full_name;
        $this->phone = (string) $lead->phone;
        $this->email = (string) $lead->email;
        foreach ([
            'icNumber' => 'ic_number', 'homeAddress' => 'home_address', 'occupation' => 'occupation',
            'employer' => 'employer', 'licenseNumber' => 'license_number', 'emergencyName' => 'emergency_name',
            'emergencyRelation' => 'emergency_relation', 'emergencyPhone' => 'emergency_phone', 'socialMedia' => 'social_media',
        ] as $prop => $col) {
            $this->{$prop} = (string) ($lead->{$col} ?? '');
        }
        $this->dateOfBirth = (string) optional($lead->date_of_birth)->format('Y-m-d');
        $this->licenseExpiry = (string) optional($lead->license_expiry)->format('Y-m-d');
    }

    protected function lead(): Lead
    {
        return Lead::where('access_token', $this->token)->with('carRate', 'documents')->firstOrFail();
    }

    public function screen(Lead $lead): string
    {
        return match (true) {
            $lead->stage === 'batal' => 'cancelled',
            $lead->stage === 'ditolak' => 'rejected',
            in_array($lead->stage, ['permohonan', 'tawaran', 'kelayakan'], true) => 'verify',
            $lead->stage === 'semakan' => 'reviewing',
            $lead->stage === 'bayaran' => $lead->payment_submitted_at ? 'payment_review' : 'pay',
            in_array($lead->stage, ['disahkan', 'diserah'], true) => 'confirmed',
            default => 'completed',
        };
    }

    protected function verificationRules(Lead $lead): array
    {
        $minExpiry = optional($lead->end_date)->format('Y-m-d') ?: now()->format('Y-m-d');

        return [
            'fullName' => ['required', 'string', 'max:150'],
            'icNumber' => ['required', 'string', 'max:30', 'regex:/^([0-9]{6}-?[0-9]{2}-?[0-9]{4}|[A-Za-z0-9]{6,20})$/'],
            'dateOfBirth' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now()->subYears(18)->format('Y-m-d')],
            'phone' => ['required', 'string', 'max:30', function ($a, $v, $fail) {
                if (! RequestForm::isValidPhone((string) $v)) {
                    $fail('Nombor telefon tidak sah.');
                }
            }],
            'email' => ['required', 'email', 'max:150'],
            'homeAddress' => ['required', 'string', 'min:10', 'max:500'],
            'occupation' => ['required', 'string', 'max:120'],
            'employer' => ['required', 'string', 'max:150'],
            'licenseNumber' => ['required', 'string', 'max:40'],
            'licenseExpiry' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$minExpiry],
            'emergencyName' => ['required', 'string', 'max:120'],
            'emergencyRelation' => ['required', 'string', 'max:60'],
            'emergencyPhone' => ['required', 'string', 'max:30', 'different:phone', function ($a, $v, $fail) {
                if (! RequestForm::isValidPhone((string) $v)) {
                    $fail('Nombor telefon waris tidak sah.');
                }
            }],
            'socialMedia' => ['nullable', 'string', 'max:200'],
            'icFront' => ['required', ...self::FILE_RULE],
            'icBack' => ['nullable', ...self::FILE_RULE],
            'license' => ['required', ...self::FILE_RULE],
            'bill' => ['required', ...self::FILE_RULE],
            'otherDoc' => ['nullable', ...self::FILE_RULE],
            'agreeTerms' => ['accepted'],
            'agreeFuel' => ['accepted'],
            'agreeDeposit' => ['accepted'],
            'agreeCancel' => ['accepted'],
            'agreeUsage' => ['accepted'],
            'signature' => ['required', 'string', 'min:3', 'max:150'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Ruangan ini wajib diisi.',
            'accepted' => 'Sila tandakan persetujuan ini.',
            'email' => 'Alamat e-mel tidak sah.',
            'icNumber.regex' => 'No. IC / pasport tidak sah (contoh IC: 900101-14-5678).',
            'dateOfBirth.before_or_equal' => 'Penyewa mesti berumur sekurang-kurangnya 18 tahun.',
            'licenseExpiry.after_or_equal' => 'Lesen memandu mesti sah sehingga tarikh pulang kereta.',
            'emergencyPhone.different' => 'Nombor waris mesti berbeza daripada nombor anda.',
            'homeAddress.min' => 'Sila isi alamat penuh.',
            'mimes' => 'Format fail mesti JPG, PNG, WEBP, HEIC atau PDF.',
            'max' => 'Saiz fail maksimum 10MB.',
            'uploaded' => 'Muat naik gagal. Sila cuba lagi (maksimum 10MB).',
            'date_format' => 'Tarikh tidak sah.',
            'signature.min' => 'Sila taip nama penuh anda sebagai tandatangan.',
        ];
    }

    protected function storeFile(Lead $lead, UploadedFile $file, string $type): LeadDocument
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $path = $file->storeAs('bookings/'.$lead->id, $type.'-'.Str::random(16).'.'.$ext, 'local');

        return $lead->documents()->create([
            'type' => $type,
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 180, ''),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function submitVerification(): void
    {
        $lead = $this->lead();
        abort_unless($this->screen($lead) === 'verify', 403);

        $this->validate($this->verificationRules($lead), $this->messages());

        // Replace any earlier copies (re-submission after the PIC asked for new documents)
        $types = array_filter([
            'ic_front' => $this->icFront, 'ic_back' => $this->icBack, 'license' => $this->license,
            'bill' => $this->bill, 'other' => $this->otherDoc,
        ]);
        foreach ($lead->documents()->whereIn('type', array_keys($types))->get() as $old) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($old->path);
            $old->delete();
        }
        foreach ($types as $type => $file) {
            $this->storeFile($lead, $file, $type);
        }

        $lead->fill([
            'full_name' => $this->fullName,
            'ic_number' => strtoupper(trim($this->icNumber)),
            'date_of_birth' => $this->dateOfBirth,
            'phone' => $this->phone,
            'email' => $this->email,
            'home_address' => $this->homeAddress,
            'occupation' => $this->occupation,
            'employer' => $this->employer,
            'license_number' => strtoupper(trim($this->licenseNumber)),
            'license_expiry' => $this->licenseExpiry,
            'emergency_name' => $this->emergencyName,
            'emergency_relation' => $this->emergencyRelation,
            'emergency_phone' => $this->emergencyPhone,
            'social_media' => filled($this->socialMedia) ? $this->socialMedia : null,
            'verification_submitted_at' => now(),
            'terms_accepted_at' => now(),
            'terms_signature' => $this->signature,
            'terms_ip' => request()->ip(),
        ]);
        $lead->customer_agreed_at ??= now();
        $lead->check('pelanggan_setuju', 'Pelanggan')
            ->check('dokumen_diterima', 'Pelanggan')
            ->check('terma_diterima', 'Pelanggan');
        $lead->moveTo('semakan');
        $lead->save();
        $lead->logEvent('kelayakan_dihantar', 'Pelanggan menghantar borang kelayakan, '.count($types).' dokumen & menerima Terma & Syarat');

        $this->notifyPic($lead, 'documents');

        $this->reset(['icFront', 'icBack', 'license', 'bill', 'otherDoc']);
        $this->justSubmitted = true;
        $this->dispatch('portal-updated');
    }

    public function submitPayment(): void
    {
        $lead = $this->lead();
        abort_unless($this->screen($lead) === 'pay', 403);

        $this->validate([
            'receipt' => ['required', ...self::FILE_RULE],
            'paymentReference' => ['nullable', 'string', 'max:120'],
        ], $this->messages());

        $this->storeFile($lead, $this->receipt, 'receipt');
        $lead->fill([
            'payment_submitted_at' => now(),
            'payment_amount' => $lead->amountDue(),
            'payment_reference' => filled($this->paymentReference) ? $this->paymentReference : null,
        ]);
        $lead->check('resit_diterima', 'Pelanggan');
        $lead->save();
        $lead->logEvent('resit_dihantar', 'Pelanggan memuat naik resit bayaran '.\App\Support\Pricing::rm($lead->amountDue()));

        $this->notifyPic($lead, 'payment');

        $this->reset(['receipt', 'paymentReference']);
        $this->justSubmitted = true;
        $this->dispatch('portal-updated');
    }

    protected function notifyPic(Lead $lead, string $type): void
    {
        try {
            Mail::to(PageSetting::current()->admin_notification_email)->send(new BookingAlert($lead, $type));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function render()
    {
        $lead = $this->lead();
        $settings = PageSetting::current();

        return view('livewire.public.booking-portal', [
            'lead' => $lead,
            'view' => $this->screen($lead),
            'settings' => $settings,
            'stageIndex' => BookingSop::stageIndex($lead->stage),
        ])->layout('layouts.corporate', [
            'title' => 'Tempahan '.$lead->referenceNumber().' | SEWOLAH',
            'description' => 'Semakan kelayakan, terma & bayaran tempahan SEWOLAH.',
            // No tracking pixel here: the URL contains the customer's private access token.
            'pixelSettings' => null,
            'pageSettings' => $settings,
            'bodyClass' => 'page-form page-portal',
            'hideMobileCta' => true,
            'noindex' => true,
        ]);
    }
}
