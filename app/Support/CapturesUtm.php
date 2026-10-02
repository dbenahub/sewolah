<?php

namespace App\Support;

trait CapturesUtm
{
    /**
     * Capture UTM / ad-click parameters on the first page view of a session so
     * they can be attached to the lead when the booking form is submitted.
     */
    protected function captureUtm(): void
    {
        $hasCampaignParams = collect(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'])
            ->contains(fn ($key) => filled(request($key)));

        if (session()->has('utm_captured') && ! $hasCampaignParams) {
            return;
        }

        session([
            'utm' => [
                'utm_source' => request('utm_source'),
                'utm_medium' => request('utm_medium'),
                'utm_campaign' => request('utm_campaign'),
                'utm_content' => request('utm_content'),
                'utm_term' => request('utm_term'),
                'fbclid' => request('fbclid'),
                'referrer' => request()->headers->get('referer'),
                'landing_page_url' => request()->fullUrl(),
            ],
            'utm_captured' => true,
        ]);
    }
}
