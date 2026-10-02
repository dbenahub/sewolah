<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class BookingPolicy
{
    public static function minWorkingDays(): int
    {
        return max(0, (int) config('sewolah.min_working_days', 3));
    }

    /**
     * Earliest pickup / arrival date a customer may request today.
     * Counts working days (Mon–Fri) after today.
     */
    public static function earliestPickupDate(?Carbon $from = null): Carbon
    {
        $from = ($from ?? now())->copy()->startOfDay();

        return $from->addWeekdays(static::minWorkingDays());
    }

    public static function earliestPickupDateString(): string
    {
        return static::earliestPickupDate()->toDateString();
    }

    public static function earliestPickupDateLabel(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return static::earliestPickupDate()->locale($locale === 'en' ? 'en' : 'ms')->translatedFormat('l, j F Y');
    }
}
