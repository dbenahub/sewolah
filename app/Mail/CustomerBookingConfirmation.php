<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerBookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead, public string $whatsappUrl) {}

    public function build()
    {
        $subject = $this->lead->locale === 'en'
            ? 'SEWOLAH — We received your booking enquiry'
            : 'SEWOLAH — Borang Tempahan Anda Telah Diterima';

        return $this->subject($subject)
            ->view('emails.customer-confirmation');
    }
}
