<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Customer emails: eligibility approved (pay now) and booking confirmed. */
class BookingCustomerUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead, public string $type) {}

    public function build()
    {
        $subject = match ($this->type) {
            'approved' => 'Kelayakan diluluskan — sila buat bayaran',
            'confirmed' => 'Tempahan anda disahkan',
            default => 'Kemas kini tempahan',
        };

        return $this->subject('SEWOLAH — '.$subject.' ['.$this->lead->referenceNumber().']')
            ->view('emails.booking-customer');
    }
}
