<?php

namespace App\Models;

use App\Support\CarCatalog;
use App\Support\Pricing;
use Illuminate\Database\Eloquent\Model;

class CarRate extends Model
{
    protected $fillable = [
        'brand', 'model', 'category', 'agent_rate', 'vip_rate', 'retail_min', 'retail_max',
        'list_price', 'deposit', 'notes', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'agent_rate' => 'integer',
            'vip_rate' => 'integer',
            'retail_min' => 'integer',
            'retail_max' => 'integer',
            'list_price' => 'integer',
            'deposit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function name(): string
    {
        return trim($this->brand.' '.$this->model);
    }

    public function categoryLabel(): string
    {
        return CarCatalog::categoryLabel($this->category);
    }

    /** Harga asal (shown struck through to the customer). */
    public function listDaily(): int
    {
        return Pricing::listPrice($this);
    }

    /** Harga booking SEWOLAH (what the customer actually pays per day). */
    public function bookingDaily(): int
    {
        return Pricing::bookingPrice($this);
    }

    public function depositAmount(int $days = 1): int
    {
        return Pricing::deposit($this, $days);
    }

    public function packages(): array
    {
        return Pricing::packages($this);
    }

    public static function activeGrouped()
    {
        return static::where('is_active', true)->orderBy('brand')->orderBy('sort_order')->orderBy('model')->get()->groupBy('brand');
    }
}
