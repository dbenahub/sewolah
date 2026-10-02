<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'source', 'customer_category', 'company_name', 'driver_license',
        'pickup_state', 'pickup_location', 'return_location',
        'full_name', 'phone', 'email', 'origin', 'airport', 'arrival_date', 'arrival_time',
        'end_date', 'purpose', 'vehicle_id', 'vehicle_name_snapshot', 'other_vehicle_model',
        'passengers', 'luggage', 'destination', 'notes', 'consent', 'status', 'locale',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'landing_page_url', 'referrer', 'fbclid', 'whatsapp_opened_at', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'arrival_date' => 'date',
            'end_date' => 'date',
            'consent' => 'boolean',
            'whatsapp_opened_at' => 'datetime',
            'submitted_at' => 'datetime',
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
}
