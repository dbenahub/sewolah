<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function build()
    {
        $subject = $this->lead->isGeneral()
            ? 'SEWOLAH — Permohonan Baharu ('.$this->lead->customerCategoryLabel('ms').'): '.$this->lead->full_name.' ['.$this->lead->referenceNumber().']'
            : 'SEWOLAH — Tempahan Baharu (Outstation): '.$this->lead->full_name.' ['.$this->lead->referenceNumber().']';

        return $this->subject($subject)
            ->view('emails.new-lead');
    }
}
