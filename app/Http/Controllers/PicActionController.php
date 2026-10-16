<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Support\BookingWorkflow;
use App\Support\WhatsAppTemplates;
use Illuminate\Support\Facades\URL;

/**
 * One-tap actions from the PIC notification email (signed links, no login).
 * Each action records the SOP step, then opens WhatsApp with the ready template.
 */
class PicActionController extends Controller
{
    public const ACTIONS = ['quote', 'verification'];

    public static function signedUrl(Lead $lead, string $action): string
    {
        return URL::temporarySignedRoute('pic.whatsapp', now()->addDays(30), [
            'lead' => $lead->id,
            'action' => $action,
        ]);
    }

    public function whatsapp(Lead $lead, string $action)
    {
        abort_unless(in_array($action, self::ACTIONS, true), 404);

        BookingWorkflow::recordTemplateSent($lead, $action, 'PIC (e-mel)');

        return redirect()->away(WhatsAppTemplates::url($lead->fresh(), $action));
    }
}
