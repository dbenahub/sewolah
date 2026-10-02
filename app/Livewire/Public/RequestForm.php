<?php

namespace App\Livewire\Public;

use App\Mail\CustomerBookingConfirmation;
use App\Mail\NewLeadNotification;
use App\Models\Lead;
use App\Models\PageSetting;
use App\Models\PixelSetting;
use App\Models\Vehicle;
use App\Support\BookingPolicy;
use App\Support\CapturesUtm;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * General booking application form (sewolah.com/form).
 * Open to every customer category across Peninsular Malaysia.
 */
class RequestForm extends Component
{
    use CapturesUtm;

    public int $step = 1;
    public bool $submitted = false;
    public bool $formStarted = false;
    public string $whatsappUrl = '';
    public string $reference = '';

    // Step 1 — category
    public string $customerCategory = '';
    public string $companyName = '';

    // Step 2 — rental details
    public string $purpose = '';
    public string $pickupState = '';
    public string $pickupLocation = '';
    public string $returnLocation = '';
    public string $pickupDate = '';
    public string $pickupTime = '';
    public string $returnDate = '';
    public string $vehicleId = '';
    public string $otherVehicleModel = '';
    public string $passengers = '';

    // Step 3 — applicant
    public string $fullName = '';
    public string $phone = '';
    public string $email = '';
    public string $driverLicense = '';
    public string $notes = '';
    public bool $consent = false;

    // Honeypot anti-spam field (must stay empty)
    public string $website = '';

    public function mount(): void
    {
        $this->captureUtm();

        $category = (string) (request('kategori') ?? request('category') ?? '');
        if (in_array($category, Lead::CUSTOMER_CATEGORIES, true)) {
            $this->customerCategory = $category;
        }

        $vehicle = (string) (request('kenderaan') ?? request('vehicle') ?? '');
        if ($vehicle !== '' && Vehicle::where('is_active', true)->whereKey($vehicle)->exists()) {
            $this->vehicleId = $vehicle;
        }
    }

    public function selectCategory(string $category): void
    {
        if (in_array($category, Lead::CUSTOMER_CATEGORIES, true)) {
            $this->customerCategory = $category;
            $this->resetErrorBag('customerCategory');
            $this->markStarted();
        }
    }

    protected function isCorporate(): bool
    {
        return in_array($this->customerCategory, ['corporate'], true);
    }

    protected function isOtherVehicle(): bool
    {
        return $this->vehicleId === 'other';
    }

    public function rulesForStep(int $step): array
    {
        $earliest = BookingPolicy::earliestPickupDateString();

        return match ($step) {
            1 => [
                'customerCategory' => ['required', Rule::in(Lead::CUSTOMER_CATEGORIES)],
                'companyName' => [$this->isCorporate() ? 'required' : 'nullable', 'string', 'max:150'],
            ],
            2 => [
                'purpose' => ['required', 'string', 'max:100'],
                'pickupState' => ['required', 'string', Rule::in(trans('form.states', [], 'ms'))],
                'pickupLocation' => ['required', 'string', 'max:150'],
                'returnLocation' => ['nullable', 'string', 'max:150'],
                'pickupDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$earliest],
                'pickupTime' => ['required', 'date_format:H:i'],
                'returnDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:pickupDate'],
                'vehicleId' => ['required', 'string', function ($attribute, $value, $fail) {
                    if (in_array($value, ['any', 'other'], true)) {
                        return;
                    }
                    if (! ctype_digit((string) $value) || ! Vehicle::where('is_active', true)->whereKey($value)->exists()) {
                        $fail(__('form.errors.required'));
                    }
                }],
                'otherVehicleModel' => [$this->isOtherVehicle() ? 'required' : 'nullable', 'string', 'max:150'],
                'passengers' => ['required', 'integer', 'min:1', 'max:50'],
            ],
            3 => [
                'fullName' => ['required', 'string', 'max:150'],
                'phone' => ['required', 'string', 'max:30', function ($attribute, $value, $fail) {
                    if (! static::isValidPhone((string) $value)) {
                        $fail(__('form.errors.phone'));
                    }
                }],
                'email' => ['required', 'email', 'max:150'],
                'driverLicense' => ['required', 'in:malaysia,international'],
                'notes' => ['nullable', 'string', 'max:1500'],
                'consent' => ['accepted'],
            ],
            default => [],
        };
    }

    public static function isValidPhone(string $phone): bool
    {
        $normalized = preg_replace('/[\s\-()]/', '', $phone);

        return (bool) preg_match('/^(\+?6?01[0-46-9][0-9]{7,8}|\+[1-9][0-9]{7,14})$/', $normalized);
    }

    protected function messagesForStep(): array
    {
        $earliestLabel = BookingPolicy::earliestPickupDateLabel();

        return [
            'required' => __('form.errors.required'),
            'customerCategory.required' => __('form.errors.category'),
            'customerCategory.in' => __('form.errors.category'),
            'pickupState.in' => __('form.errors.required'),
            'pickupDate.after_or_equal' => __('form.errors.min_date', ['date' => $earliestLabel]),
            'pickupDate.date_format' => __('form.errors.date'),
            'returnDate.date_format' => __('form.errors.date'),
            'returnDate.after_or_equal' => __('form.errors.return_date'),
            'pickupTime.date_format' => __('form.errors.required'),
            'passengers.integer' => __('form.errors.passengers'),
            'passengers.min' => __('form.errors.passengers'),
            'passengers.max' => __('form.errors.passengers'),
            'email.email' => __('form.errors.email'),
            'driverLicense.in' => __('form.errors.required'),
            'consent.accepted' => __('form.errors.consent'),
        ];
    }

    protected function validateStep(int $step): void
    {
        $this->validate($this->rulesForStep($step), $this->messagesForStep());
    }

    protected function markStarted(): void
    {
        if (! $this->formStarted) {
            $this->formStarted = true;
            $this->dispatch('form-start');
        }
    }

    public function updated($name): void
    {
        $this->markStarted();
    }

    public function goNext(): void
    {
        $this->validateStep($this->step);
        $this->step = min(3, $this->step + 1);
        $this->dispatch('form-step-changed');
    }

    public function goBack(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->dispatch('form-step-changed');
    }

    public function submit(): void
    {
        // Honeypot: if filled, silently pretend success (bot trap)
        if (filled($this->website)) {
            $this->submitted = true;
            return;
        }

        // Re-validate every step server-side before saving.
        foreach ([1, 2, 3] as $step) {
            try {
                $this->validateStep($step);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->step = $step;
                throw $e;
            }
        }

        $vehicle = ($this->vehicleId !== '' && ctype_digit($this->vehicleId))
            ? Vehicle::find($this->vehicleId)
            : null;

        $vehicleSnapshot = match (true) {
            $vehicle !== null => $vehicle->name,
            $this->isOtherVehicle() => $this->otherVehicleModel,
            default => trans('form.fields.vehicle_any', [], 'ms'),
        };

        $utm = session('utm', []);

        $lead = Lead::create([
            'source' => 'general',
            'customer_category' => $this->customerCategory,
            'company_name' => $this->isCorporate() ? $this->companyName : (filled($this->companyName) ? $this->companyName : null),
            'driver_license' => $this->driverLicense,
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'email' => $this->email,
            'pickup_state' => $this->pickupState,
            'pickup_location' => $this->pickupLocation,
            'return_location' => filled($this->returnLocation) ? $this->returnLocation : null,
            'arrival_date' => $this->pickupDate,
            'arrival_time' => $this->pickupTime,
            'end_date' => $this->returnDate,
            'purpose' => $this->purpose,
            'vehicle_id' => $vehicle?->id,
            'vehicle_name_snapshot' => $vehicleSnapshot,
            'other_vehicle_model' => $this->isOtherVehicle() ? $this->otherVehicleModel : null,
            'passengers' => (int) $this->passengers,
            'notes' => filled($this->notes) ? $this->notes : null,
            'consent' => true,
            'status' => 'baru',
            'locale' => App::getLocale(),
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content' => $utm['utm_content'] ?? null,
            'utm_term' => $utm['utm_term'] ?? null,
            'landing_page_url' => $utm['landing_page_url'] ?? request()->headers->get('referer'),
            'referrer' => $utm['referrer'] ?? null,
            'fbclid' => $utm['fbclid'] ?? null,
            'submitted_at' => now(),
        ]);

        $settings = PageSetting::current();
        $this->reference = $lead->referenceNumber();
        $this->whatsappUrl = $this->buildWhatsappUrl($lead, (string) $settings->whatsapp_number);

        try {
            Mail::to($settings->admin_notification_email)->send(new NewLeadNotification($lead));
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            Mail::to($lead->email)->send(new CustomerBookingConfirmation($lead, $this->whatsappUrl));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->submitted = true;
        $this->dispatch('form-step-changed');
        $this->dispatch('lead-submitted', eventId: 'lead_'.$lead->id.'_'.Str::uuid());
    }

    protected function buildWhatsappUrl(Lead $lead, string $number): string
    {
        $message = __('form.whatsapp_message', [
            'ref' => $lead->referenceNumber(),
            'name' => $lead->full_name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'category' => __('form.categories.'.$lead->customer_category.'.title'),
            'company' => $lead->company_name ?: '-',
            'purpose' => $lead->purpose,
            'state' => $lead->pickup_state,
            'pickup_location' => $lead->pickup_location,
            'return_location' => $lead->return_location ?: '-',
            'pickup' => trim($lead->arrival_date?->format('d/m/Y').' '.substr((string) $lead->arrival_time, 0, 5)),
            'return_date' => $lead->end_date?->format('d/m/Y'),
            'vehicle' => $lead->vehicle_name_snapshot,
            'passengers' => $lead->passengers,
            'license' => __('form.license_options.'.$lead->driver_license),
            'notes' => $lead->notes ?: '-',
        ]);

        return 'https://wa.me/'.preg_replace('/[^0-9]/', '', $number).'?text='.rawurlencode($message);
    }

    #[Computed]
    public function vehicleOptions()
    {
        return Vehicle::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function render()
    {
        return view('livewire.public.request-form', [
            'earliestDate' => BookingPolicy::earliestPickupDateString(),
            'earliestLabel' => BookingPolicy::earliestPickupDateLabel(),
            'minDays' => BookingPolicy::minWorkingDays(),
        ])->layout('layouts.corporate', [
            'title' => __('form.meta_title'),
            'description' => __('form.meta_description'),
            'pixelSettings' => PixelSetting::current(),
            'pageSettings' => PageSetting::current(),
            'bodyClass' => 'page-form',
            'hideMobileCta' => true,
        ]);
    }
}
