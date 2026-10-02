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
        $ref = ' ['.$this->lead->referenceNumber().']';

        if ($this->lead->isGeneral()) {
            $subject = $this->lead->locale === 'en'
                ? 'SEWOLAH — We received your booking application'.$ref
                : 'SEWOLAH — Permohonan Tempahan Anda Telah Diterima'.$ref;
        } else {
            $subject = $this->lead->locale === 'en'
                ? 'SEWOLAH — We received your booking enquiry'.$ref
                : 'SEWOLAH — Borang Tempahan Anda Telah Diterima'.$ref;
        }

        return $this->subject($subject)
            ->view('emails.customer-confirmation');
    }
}
