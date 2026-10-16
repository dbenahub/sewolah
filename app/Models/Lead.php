<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\BookingSop;
use App\Support\Pricing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory;

    /** All columns except the key are assignable; inputs are always validated/whitelisted by callers. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'arrival_date' => 'date',
            'end_date' => 'date',
            'consent' => 'boolean',
            'whatsapp_opened_at' => 'datetime',
            'submitted_at' => 'datetime',
            'quote_sent_at' => 'datetime',
            'customer_agreed_at' => 'datetime',
            'verification_link_sent_at' => 'datetime',
            'date_of_birth' => 'date',
            'license_expiry' => 'date',
            'verification_submitted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'verification_approved_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'handover_at' => 'datetime',
            'returned_at' => 'datetime',
            'deposit_refunded_at' => 'datetime',
            'sop_checks' => 'array',
        ];
    }

    public const CUSTOMER_CATEGORIES = ['individual', 'corporate', 'outstation', 'event', 'long_term'];

    public function referenceNumber(): string
    {
        return 'SWL-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function isGeneral(): bool
    {
        return $this->source === 'general';
    }

    public function sourceLabel(): string
    {
        return $this->source === 'general' ? 'Borang Utama (/form)' : 'Outstation (/outstation)';
    }

    public function customerCategoryLabel(?string $locale = 'ms'): ?string
    {
        if (blank($this->customer_category)) {
            return null;
        }

        return trans('form.categories.'.$this->customer_category.'.title', [], $locale ?? 'ms');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function carRate()
    {
        return $this->belongsTo(CarRate::class);
    }

    public function documents()
    {
        return $this->hasMany(LeadDocument::class);
    }

    public function events()
    {
        return $this->hasMany(LeadEvent::class)->latest('id');
    }

    /* ---------------------------------------------------------------
     | Booking workflow
     * -------------------------------------------------------------- */

    public function stageLabel(): string
    {
        return BookingSop::stageLabel($this->stage);
    }

    public function compliance(): array
    {
        return BookingSop::compliance($this);
    }

    public function ensureAccessToken(): string
    {
        if (! $this->access_token) {
            $this->forceFill(['access_token' => Str::random(48)])->save();
        }

        return $this->access_token;
    }

    public function portalUrl(): string
    {
        return route('booking.portal', $this->ensureAccessToken());
    }

    public function isChecked(string $key): bool
    {
        return isset(($this->sop_checks ?? [])[$key]);
    }

    public function checkInfo(string $key): ?array
    {
        return ($this->sop_checks ?? [])[$key] ?? null;
    }

    /** Tick an SOP item (no-op if already ticked). Does not save. */
    public function check(string $key, ?string $by = null): static
    {
        $checks = $this->sop_checks ?? [];
        if (! isset($checks[$key])) {
            $checks[$key] = ['at' => now()->toIso8601String(), 'by' => $by ?? (Auth::user()?->name ?? 'Sistem')];
            $this->sop_checks = $checks;
        }

        return $this;
    }

    public function uncheck(string $key): static
    {
        $checks = $this->sop_checks ?? [];
        unset($checks[$key]);
        $this->sop_checks = $checks;

        return $this;
    }

    public function logEvent(string $type, string $message): LeadEvent
    {
        return $this->events()->create([
            'type' => $type,
            'message' => Str::limit($message, 250),
            'user_id' => Auth::id(),
        ]);
    }

    /** Move to a workflow stage and keep the legacy status in sync. Does not save. */
    public function moveTo(string $stage): static
    {
        if (array_key_exists($stage, BookingSop::STAGES)) {
            $this->stage = $stage;
            $this->status = BookingSop::STATUS_FOR_STAGE[$stage] ?? $this->status;
        }

        return $this;
    }

    /** Only move forward (never back) unless the booking is closed. */
    public function advanceTo(string $stage): static
    {
        if (BookingSop::isClosed($this->stage)) {
            return $this;
        }
        if (BookingSop::stageIndex($stage) > BookingSop::stageIndex($this->stage)) {
            $this->moveTo($stage);
        }

        return $this;
    }

    /**
     * Calculate the quotation from the selected car and rental dates.
     * $daily overrides the booking price (e.g. a special offer). Does not save.
     */
    public function applyQuote(?CarRate $rate = null, ?int $daily = null): static
    {
        $rate ??= $this->carRate;
        $days = Pricing::days($this->arrival_date, $this->end_date);
        $this->rental_days = $days;

        if (! $rate) {
            return $this;
        }

        $this->car_rate_id = $rate->id;
        $daily = $daily ?: $rate->bookingDaily();
        $calc = Pricing::rentalTotal($daily, $days);

        $this->quoted_list_daily = max($rate->listDaily(), $daily);
        $this->quoted_daily = $daily;
        $this->quoted_rental_total = $calc['total'];
        $this->quote_package = $calc['package'];
        $this->deposit_amount = $rate->depositAmount($days);

        return $this;
    }

    public function hasQuote(): bool
    {
        return (bool) $this->quoted_rental_total;
    }

    public function amountDue(): int
    {
        return (int) $this->quoted_rental_total + (int) $this->deposit_amount + (int) $this->delivery_fee;
    }

    public function vehicleName(): string
    {
        return $this->carRate?->name() ?? ($this->vehicle_name_snapshot ?: '-');
    }

    public function customerWaNumber(): string
    {
        $n = preg_replace('/[^0-9]/', '', (string) $this->phone);
        if (str_starts_with($n, '0')) {
            $n = '6'.$n;
        }

        return $n;
    }

    public function pickupLabel(): string
    {
        $time = $this->arrival_time ? ', '.substr((string) $this->arrival_time, 0, 5) : '';

        return trim(optional($this->arrival_date)->format('d/m/Y').$time);
    }

    public function pickupPlace(): string
    {
        if ($this->isGeneral()) {
            return trim(($this->pickup_location ?: '').($this->pickup_state ? ', '.$this->pickup_state : ''), ', ');
        }

        return $this->airport ?: ($this->origin ?: '-');
    }

    public function depositRefundDue(): ?\Illuminate\Support\Carbon
    {
        return $this->returned_at?->copy()->addDays((int) Pricing::setting('deposit_refund_days'));
    }
}
