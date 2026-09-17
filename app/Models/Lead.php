<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
