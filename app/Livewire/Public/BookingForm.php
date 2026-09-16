<?php

namespace App\Livewire\Public;

use App\Mail\NewLeadNotification;
use App\Models\Lead;
use App\Models\PageSetting;
use App\Models\Vehicle;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingForm extends Component
{
    public int $step = 1;
    public bool $submitted = false;
    public bool $formStarted = false;

    // Step 1
    public string $origin = '';
    public string $airport = '';
    public string $purpose = '';
    public string $arrivalDate = '';
    public string $arrivalTime = '';
    public string $endDate = '';

    // Step 2
    public ?int $vehicleId = null;
    public string $otherVehicleModel = '';
    public string $passengers = '';
    public string $luggage = '';
    public string $destination = '';

    // Step 3
    public string $fullName = '';
    public string $phone = '';
    public string $notes = '';
    public bool $consent = false;

    // Honeypot anti-spam field (must stay empty)
    public string $website = '';

    public function selectVehicle(int $vehicleId)
    {
        $this->vehicleId = $vehicleId;
        $this->dispatch('scroll-to-booking-form');
    }

    protected function isOtherVehicle(): bool
    {
        $vehicle = $this->vehicleId ? Vehicle::find($this->vehicleId) : null;

        return $vehicle && str_contains(strtolower($vehicle->name), 'other');
    }

    public function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'origin' => ['required', 'string', 'max:150'],
                'airport' => ['required', 'string'],
                'purpose' => ['required', 'string'],
                'arrivalDate' => ['required', 'date'],
                'arrivalTime' => ['required'],
                'endDate' => ['required', 'date', 'after_or_equal:arrivalDate'],
            ],
            2 => [
                'vehicleId' => ['required'],
                'otherVehicleModel' => [$this->isOtherVehicle() ? 'required' : 'nullable', 'string', 'max:150'],
                'passengers' => ['required', 'integer', 'min:1', 'max:20'],
                'luggage' => ['required', 'string'],
                'destination' => ['required', 'string', 'max:150'],
            ],
            3 => [
                'fullName' => ['required', 'string', 'max:150'],
                'phone' => ['required', 'regex:/^(\+?6?01)[0-46-9]-*[0-9]{7,8}$/'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'consent' => ['accepted'],
            ],
            default => [],
        };
    }

    public function updated($name)
    {
        if (! $this->formStarted && $this->step === 1) {
            $this->formStarted = true;
            $this->dispatch('form-start');
        }
    }

    public function goNext()
    {
        $this->validate($this->rulesForStep($this->step));
        $this->step = min(3, $this->step + 1);
    }

    public function goBack()
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit()
    {
        // Honeypot: if filled, silently pretend success (bot trap)
        if (filled($this->website)) {
            $this->submitted = true;
            return;
        }

        $this->validate($this->rulesForStep(3));

        $vehicle = $this->vehicleId ? Vehicle::find($this->vehicleId) : null;
        $utm = session('utm', []);

        $lead = Lead::create([
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'origin' => $this->origin,
            'airport' => $this->airport,
            'arrival_date' => $this->arrivalDate,
            'arrival_time' => $this->arrivalTime,
            'end_date' => $this->endDate,
            'purpose' => $this->purpose,
            'vehicle_id' => $vehicle?->id,
            'vehicle_name_snapshot' => $vehicle?->name ?? $this->otherVehicleModel,
            'other_vehicle_model' => $this->otherVehicleModel,
            'passengers' => $this->passengers,
            'luggage' => $this->luggage,
            'destination' => $this->destination,
            'notes' => $this->notes,
            'consent' => true,
            'status' => 'baru',
            'locale' => App::getLocale(),
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content' => $utm['utm_content'] ?? null,
            'utm_term' => $utm['utm_term'] ?? null,
            'landing_page_url' => $utm['landing_page_url'] ?? null,
            'referrer' => $utm['referrer'] ?? null,
            'fbclid' => $utm['fbclid'] ?? null,
            'submitted_at' => now(),
        ]);

        try {
            $settings = PageSetting::current();
            Mail::to($settings->admin_notification_email)->queue(new NewLeadNotification($lead));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->submitted = true;

        $waNumber = PageSetting::current()->whatsapp_number;
        $this->dispatch('open-whatsapp', url: $this->buildWhatsappUrl($lead, $waNumber));
        $this->dispatch('lead-submitted');
    }

    protected function buildWhatsappUrl(Lead $lead, string $number): string
    {
        $message = __('landing.whatsapp_message', [
            'name' => $lead->full_name,
            'origin' => $lead->origin,
            'airport' => $lead->airport,
            'arrival' => trim($lead->arrival_date?->format('d/m/Y').' '.$lead->arrival_time),
            'end_date' => $lead->end_date?->format('d/m/Y'),
            'purpose' => $lead->purpose,
            'vehicle' => $lead->vehicle_name_snapshot,
            'other_vehicle' => $lead->other_vehicle_model ?: '-',
            'passengers' => $lead->passengers,
            'luggage' => $lead->luggage,
            'destination' => $lead->destination,
            'notes' => $lead->notes ?: '-',
        ]);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    #[Computed]
    public function vehicleOptions()
    {
        return Vehicle::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function render()
    {
        return view('livewire.public.booking-form');
    }
}
