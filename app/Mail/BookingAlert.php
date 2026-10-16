<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Alerts the PIC when the customer submits documents or a payment receipt. */
class BookingAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead, public string $type) {}

    public function build()
    {
        $what = $this->type === 'payment' ? 'Resit bayaran diterima' : 'Dokumen kelayakan diterima';

        return $this->subject('SEWOLAH — '.$what.': '.$this->lead->full_name.' ['.$this->lead->referenceNumber().']')
            ->view('emails.booking-alert');
    }
}
