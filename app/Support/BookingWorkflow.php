<?php

namespace App\Support;

use App\Models\Lead;

/**
 * Side effects of the booking workflow shared by the admin panel,
 * the PIC email links and the customer portal.
 */
class BookingWorkflow
{
    /** Record that a WhatsApp template was sent and advance the stage. */
    public static function recordTemplateSent(Lead $lead, string $template, ?string $by = null): void
    {
        switch ($template) {
            case 'quote':
                if (! $lead->hasQuote()) {
                    $lead->applyQuote();
                }
                $lead->check('hantar_tawaran', $by);
                $lead->quote_sent_at ??= now();
                $lead->advanceTo('tawaran');
                break;
            case 'verification':
                $lead->ensureAccessToken();
                $lead->check('pelanggan_setuju', $by)->check('hantar_pautan', $by);
                $lead->customer_agreed_at ??= now();
                $lead->verification_link_sent_at = now();
                $lead->advanceTo('kelayakan');
                break;
            case 'approved':
                $lead->check('jumlah_dimaklumkan', $by);
                break;
            case 'confirmed':
                $lead->check('mesej_pengesahan', $by);
                break;
            case 'handover':
                $lead->check('mesej_sebelum_serahan', $by);
                break;
            case 'return':
                $lead->check('sahkan_masa_pulang', $by);
                break;
            case 'thanks':
                $lead->check('mesej_terima_kasih', $by);
                break;
        }

        $lead->save();
        $lead->logEvent('whatsapp_'.$template, 'WhatsApp dihantar: '.(WhatsAppTemplates::LABELS[$template] ?? $template));
    }
}
