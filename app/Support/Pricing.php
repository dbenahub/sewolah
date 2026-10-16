<?php

namespace App\Support;

use App\Models\CarRate;
use App\Models\PageSetting;
use Illuminate\Support\Carbon;

/**
 * SEWOLAH pricing rules.
 *
 * - Harga booking (daily)  = lowest retail price.
 * - Harga asal (daily)     = highest retail price when a range exists,
 *                            otherwise booking price + list markup (default 20%),
 *                            rounded to the nearest RM10. Can be overridden per car.
 * - Packages (from the booking price):
 *     3 hari    = 3 × daily − 5%
 *     Mingguan  = daily × 6
 *     Bulanan   = daily × 20
 *     Tahunan   = monthly × 10
 * - Deposit: per car override, else by category; rentals of 30 days or more
 *   require one month of rent as deposit (GMHUB NOTA 129).
 * All values are editable in Admin → Harga & Bayaran.
 */
class Pricing
{
    public const DEFAULTS = [
        'list_markup' => 20,          // % above booking price for single-price cars
        'three_day_discount' => 5,    // % off for 3–6 day rentals
        'weekly_days' => 6,           // a week costs this many days
        'monthly_days' => 20,         // a month (30 days) costs this many days
        'yearly_months' => 10,        // a year costs this many months
        'extra_hour_divisor' => 12,   // extra hour = daily ÷ 12
        'quote_hold_hours' => 24,     // how long a quotation is held
        'deposit_refund_days' => 14,  // deposit refunded within N days of return
        'deposits' => [],             // category => RM (falls back to CarCatalog)
    ];

    public const PACKAGE_LABELS = [
        'harian' => 'Harian',
        '3hari' => 'Pakej 3 hari+',
        'mingguan' => 'Mingguan',
        'bulanan' => 'Bulanan',
        'tahunan' => 'Tahunan',
    ];

    protected static ?array $cache = null;

    public static function settings(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $stored = [];
        try {
            $stored = (array) (PageSetting::current()->pricing ?? []);
        } catch (\Throwable $e) {
            $stored = [];
        }

        $merged = array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null && $v !== ''));
        $deposits = [];
        foreach (CarCatalog::CATEGORIES as $key => [$label, $default]) {
            $deposits[$key] = (int) ($stored['deposits'][$key] ?? $default);
        }
        $merged['deposits'] = $deposits;

        return static::$cache = $merged;
    }

    public static function flush(): void
    {
        static::$cache = null;
    }

    public static function setting(string $key): mixed
    {
        return self::settings()[$key] ?? self::DEFAULTS[$key] ?? null;
    }

    public static function roundTen(float $value): int
    {
        return (int) (round($value / 10) * 10);
    }

    public static function bookingPrice(CarRate $rate): int
    {
        return (int) $rate->retail_min;
    }

    public static function listPrice(CarRate $rate): int
    {
        if ($rate->list_price) {
            return (int) $rate->list_price;
        }
        if ($rate->retail_max && $rate->retail_max > $rate->retail_min) {
            return (int) $rate->retail_max;
        }

        return self::roundTen($rate->retail_min * (1 + self::setting('list_markup') / 100));
    }

    /** @return array{daily:int, three_day:int, weekly:int, monthly:int, yearly:int} */
    public static function packages(CarRate|int $rate): array
    {
        $daily = is_int($rate) ? $rate : self::bookingPrice($rate);
        $monthly = (int) round($daily * self::setting('monthly_days'));

        return [
            'daily' => $daily,
            'three_day' => (int) round($daily * 3 * (1 - self::setting('three_day_discount') / 100)),
            'weekly' => (int) round($daily * self::setting('weekly_days')),
            'monthly' => $monthly,
            'yearly' => (int) round($monthly * self::setting('yearly_months')),
        ];
    }

    /** Number of rental days between pickup and return (minimum 1). */
    public static function days($pickup, $return): int
    {
        if (! $pickup || ! $return) {
            return 1;
        }
        $start = Carbon::parse($pickup)->startOfDay();
        $end = Carbon::parse($return)->startOfDay();

        return max(1, (int) $start->diffInDays($end));
    }

    /**
     * Total rent for a number of days at a given daily booking price,
     * choosing the cheapest applicable package.
     *
     * @return array{total:int, package:string, effective_daily:int}
     */
    public static function rentalTotal(int $daily, int $days): array
    {
        $days = max(1, $days);
        $p = self::packages($daily);

        $candidates = [];
        $candidates['harian'] = $daily * $days;
        if ($days >= 3) {
            $candidates['3hari'] = (int) round($days * $daily * (1 - self::setting('three_day_discount') / 100));
        }
        if ($days >= 7) {
            $candidates['mingguan'] = (int) round($days * $p['weekly'] / 7);
        } elseif ($days >= 5) {
            $candidates['mingguan'] = $p['weekly'];          // a week is cheaper than 5–6 single days
        }
        if ($days >= 30) {
            $candidates['bulanan'] = (int) round($days * $p['monthly'] / 30);
        } elseif ($days >= 20) {
            $candidates['bulanan'] = $p['monthly'];
        }
        if ($days >= 365) {
            $candidates['tahunan'] = (int) round($days * $p['yearly'] / 365);
        } elseif ($days >= 300) {
            $candidates['tahunan'] = $p['yearly'];
        }

        asort($candidates);
        $package = array_key_first($candidates);
        $total = $candidates[$package];

        return [
            'total' => $total,
            'package' => $package,
            'effective_daily' => (int) round($total / $days),
        ];
    }

    public static function deposit(CarRate $rate, int $days = 1): int
    {
        if ($days >= 30) {
            return self::packages($rate)['monthly'];
        }
        if ($rate->deposit) {
            return (int) $rate->deposit;
        }

        return (int) (self::settings()['deposits'][$rate->category] ?? CarCatalog::defaultDeposit($rate->category));
    }

    public static function extraHourRate(int $daily): int
    {
        return (int) ceil($daily / max(1, (int) self::setting('extra_hour_divisor')));
    }

    public static function rm(?int $amount): string
    {
        return 'RM'.number_format((int) $amount);
    }
}
