<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\PageSetting;

/**
 * Ready-made WhatsApp messages the PIC sends to the customer at each stage.
 * WhatsApp formatting: *bold*, _italic_, ~strikethrough~.
 */
class WhatsAppTemplates
{
    public const LABELS = [
        'quote' => 'Tawaran harga & kereta available',
        'verification' => 'Pautan semakan kelayakan',
        'approved' => 'Kelayakan lulus & arahan bayaran',
        'confirmed' => 'Tempahan disahkan (bayaran diterima)',
        'handover' => 'Peringatan sebelum serahan (3–6 jam)',
        'return' => 'Peringatan pemulangan',
        'thanks' => 'Terima kasih & deposit',
        'rejected' => 'Kelayakan tidak lulus',
    ];

    protected const LINE = '━━━━━━━━━━━━━━━';

    public static function url(Lead $lead, string $template): string
    {
        return 'https://wa.me/'.$lead->customerWaNumber().'?text='.rawurlencode(static::text($lead, $template));
    }

    public static function text(Lead $lead, string $template): string
    {
        return match ($template) {
            'quote' => $lead->hasQuote() ? static::quote($lead) : static::availabilityNoPrice($lead),
            'verification' => static::verification($lead),
            'approved' => static::approved($lead),
            'confirmed' => static::confirmed($lead),
            'handover' => static::handover($lead),
            'return' => static::returnReminder($lead),
            'thanks' => static::thanks($lead),
            'rejected' => static::rejected($lead),
            default => '',
        };
    }

    protected static function pic(): string
    {
        return PageSetting::current()->picName();
    }

    protected static function signature(): string
    {
        return "Sekian, terima kasih 🙏\n*".static::pic()."*\nSEWOLAH — _Rent With Confidence_\n🌐 sewolah.com";
    }

    protected static function bookingBlock(Lead $lead): string
    {
        $lines = [
            '🔖 Rujukan: *'.$lead->referenceNumber().'*',
            '🚗 Kereta: *'.$lead->vehicleName().'*',
            '📅 Ambil: '.$lead->pickupLabel(),
            '📅 Pulang: '.(optional($lead->end_date)->format('d/m/Y') ?: '-'),
            '📍 Lokasi: '.($lead->pickupPlace() ?: '-'),
        ];
        if ($lead->rental_days) {
            $lines[] = '⏱️ Tempoh: '.$lead->rental_days.' hari';
        }

        return implode("\n", $lines);
    }

    public static function quote(Lead $lead): string
    {
        $rm = fn ($v) => Pricing::rm($v);
        $days = max(1, (int) $lead->rental_days);
        $list = (int) $lead->quoted_list_daily;
        $daily = (int) $lead->quoted_daily;
        $packageLabel = Pricing::PACKAGE_LABELS[$lead->quote_package] ?? 'Harian';

        $price = [];
        if ($list > $daily) {
            $price[] = 'Harga asal: ~'.$rm($list).'/hari~';
            $price[] = 'Harga tempahan SEWOLAH: *'.$rm($daily).'/hari* 🔥';
            $price[] = '_Jimat '.$rm($list - $daily).' sehari_';
        } else {
            $price[] = 'Harga tempahan SEWOLAH: *'.$rm($daily).'/hari*';
        }
        $price[] = '';
        $price[] = 'Sewa '.$days.' hari ('.$packageLabel.'): *'.$rm($lead->quoted_rental_total).'*';
        $price[] = 'Deposit keselamatan: '.$rm($lead->deposit_amount).' _(dikembalikan)_';
        if ($lead->delivery_fee) {
            $price[] = 'Caj penghantaran: '.$rm($lead->delivery_fee);
        }

        $p = Pricing::packages($daily);
        $packages = [
            '▫️ 3 hari: '.$rm($p['three_day']),
            '▫️ Mingguan (7 hari): '.$rm($p['weekly']),
            '▫️ Bulanan (30 hari): '.$rm($p['monthly']),
            '▫️ Tahunan (12 bulan): '.$rm($p['yearly']),
        ];

        $hold = (int) Pricing::setting('quote_hold_hours');

        return implode("\n", [
            'Assalamualaikum & salam sejahtera, *'.$lead->full_name.'* 👋',
            '',
            'Terima kasih kerana memilih *SEWOLAH*.',
            'Permohonan tempahan anda telah *disemak* ✅ dan kereta *AVAILABLE* pada tarikh yang dipohon.',
            '',
            self::LINE,
            '📋 *BUTIRAN TEMPAHAN*',
            self::LINE,
            static::bookingBlock($lead),
            '',
            self::LINE,
            '💰 *HARGA SEWA*',
            self::LINE,
            implode("\n", $price),
            self::LINE,
            '💳 *JUMLAH BAYARAN: '.$rm($lead->amountDue()).'*',
            self::LINE,
            '',
            '📦 *Pilihan pakej lain:*',
            implode("\n", $packages),
            ...($days >= 30 ? ['', '_Sewaan bulanan: deposit bersamaan 1 bulan sewa._'] : []),
            '',
            '📝 *Langkah seterusnya:*',
            '1️⃣ Balas *SETUJU* untuk teruskan tempahan',
            '2️⃣ Kami hantar pautan semakan kelayakan (IC, lesen & bil utiliti)',
            '3️⃣ Selepas lulus, buat bayaran terus melalui pautan yang sama',
            '4️⃣ Kereta diserahkan pada tarikh & lokasi yang dipersetujui',
            '',
            '⏳ Harga & kekosongan dipegang selama *'.$hold.' jam*.',
            '',
            static::signature(),
        ]);
    }

    public static function availabilityNoPrice(Lead $lead): string
    {
        return implode("\n", [
            'Assalamualaikum & salam sejahtera, *'.$lead->full_name.'* 👋',
            '',
            'Terima kasih kerana memilih *SEWOLAH*. Permohonan tempahan anda telah kami *terima & semak* ✅',
            '',
            static::bookingBlock($lead),
            '',
            'Boleh saya tahu model kereta pilihan anda? Kami akan sahkan kekosongan dan hantar harga terbaik untuk tarikh tersebut.',
            '',
            static::signature(),
        ]);
    }

    public static function verification(Lead $lead): string
    {
        return implode("\n", [
            'Terima kasih *'.$lead->full_name.'* atas persetujuan anda 🙏',
            '',
            'Langkah seterusnya ialah *semakan kelayakan penyewa*. Sila lengkapkan borang di pautan ini (±5 minit):',
            '👉 '.$lead->portalUrl(),
            '',
            '*Sila sediakan:*',
            '✅ IC / pasport (depan & belakang)',
            '✅ Lesen memandu yang sah',
            '✅ Bil utiliti terkini (TNB / air / internet)',
            '✅ Nama & nombor telefon waris terdekat',
            '',
            '🔒 Dokumen anda disimpan secara sulit dan hanya digunakan untuk semakan tempahan ini.',
            '',
            'Selepas kelayakan diluluskan, anda boleh terus membuat bayaran melalui pautan yang sama.',
            '',
            'Rujukan: *'.$lead->referenceNumber().'*',
            static::signature(),
        ]);
    }

    public static function approved(Lead $lead): string
    {
        $s = PageSetting::current();
        $bank = $s->hasBankDetails()
            ? '🏦 '.$s->bank_name.' — *'.$s->bank_account_number.'*'.($s->bank_account_name ? "\n".$s->bank_account_name : '')
            : '';

        $breakdown = 'Sewa '.Pricing::rm($lead->quoted_rental_total).' + Deposit '.Pricing::rm($lead->deposit_amount)
            .($lead->delivery_fee ? ' + Penghantaran '.Pricing::rm($lead->delivery_fee) : '');

        return implode("\n", array_filter([
            'Tahniah *'.$lead->full_name.'*! 🎉',
            'Semakan kelayakan anda telah *DILULUSKAN* ✅',
            '',
            self::LINE,
            '💳 *JUMLAH BAYARAN: '.Pricing::rm($lead->amountDue()).'*',
            '_('.$breakdown.')_',
            self::LINE,
            '',
            'Sila buat bayaran & muat naik resit di pautan ini:',
            '👉 '.$lead->portalUrl(),
            '',
            $bank,
            '🔖 Rujukan bayaran: *'.$lead->referenceNumber().'*',
            '',
            'Tempahan akan disahkan sebaik sahaja bayaran diterima.',
            '',
            static::signature(),
        ], fn ($l) => $l !== null));
    }

    public static function confirmed(Lead $lead): string
    {
        return implode("\n", [
            '✅ *TEMPAHAN DISAHKAN*',
            '',
            'Terima kasih *'.$lead->full_name.'*, bayaran anda sebanyak *'.Pricing::rm($lead->payment_amount ?: $lead->amountDue()).'* telah diterima.',
            '',
            static::bookingBlock($lead),
            '',
            '📌 *Sila bawa semasa mengambil kereta:*',
            '• IC / pasport *ASAL*',
            '• Lesen memandu *ASAL* yang sah',
            '',
            '⛽ Kereta diserahkan dengan tangki *penuh* dan perlu dipulangkan *penuh* (Full to Full).',
            '📞 Staf operasi kami akan menghubungi anda 3–6 jam sebelum serahan.',
            '',
            'Status tempahan: '.$lead->portalUrl(),
            '',
            static::signature(),
        ]);
    }

    public static function handover(Lead $lead): string
    {
        return implode("\n", [
            'Salam sejahtera *'.$lead->full_name.'* 👋',
            '',
            'Saya *'.static::pic().'* dari Pasukan Operasi SEWOLAH. Saya akan menguruskan serahan kereta *'.$lead->vehicleName().'* pada *'.$lead->pickupLabel().'* di *'.($lead->pickupPlace() ?: 'lokasi yang dipersetujui').'*.',
            '',
            'Boleh sahkan anda dijadualkan tiba seperti yang dirancang? Jika ada kelewatan (contoh: penerbangan lewat), mohon maklumkan awal supaya kami boleh atur semula.',
            '',
            '📌 Sila sediakan IC / pasport & lesen memandu *ASAL* untuk semakan semasa serahan.',
            '',
            'Simpan nombor ini untuk sebarang bantuan. Terima kasih! 🙏',
        ]);
    }

    public static function returnReminder(Lead $lead): string
    {
        return implode("\n", [
            'Salam sejahtera *'.$lead->full_name.'* 👋',
            '',
            'Peringatan mesra: tempoh sewa *'.$lead->vehicleName().'* ('.$lead->referenceNumber().') tamat pada *'.(optional($lead->end_date)->format('d/m/Y') ?: '-').'*.',
            '',
            'Mohon sahkan *masa & lokasi pemulangan* supaya staf kami bersedia menerima kereta.',
            '⛽ Kereta perlu dipulangkan dengan tangki *penuh*.',
            '🕑 Jika ingin melanjutkan sewa, maklumkan kami — lanjutan perlu dibayar terlebih dahulu.',
            '',
            'Terima kasih! 🙏',
            '*'.static::pic().'* — SEWOLAH',
        ]);
    }

    public static function thanks(Lead $lead): string
    {
        $refund = max(0, (int) $lead->deposit_amount - (int) $lead->deposit_deduction);

        return implode("\n", array_filter([
            'Terima kasih *'.$lead->full_name.'* kerana menyewa bersama *SEWOLAH* 🙏',
            '',
            $lead->deposit_refunded_at
                ? '💰 Deposit anda sebanyak *'.Pricing::rm($refund).'* telah dipulangkan.'
                : '💰 Deposit anda sebanyak *'.Pricing::rm($refund).'* akan dipulangkan dalam tempoh '.Pricing::setting('deposit_refund_days').' hari.',
            $lead->deposit_deduction ? '_Potongan '.Pricing::rm($lead->deposit_deduction).': '.$lead->deposit_deduction_reason.'_' : null,
            '',
            'Kami amat menghargai jika anda dapat berkongsi pengalaman anda — ulasan anda membantu kami terus memberi perkhidmatan terbaik ⭐',
            '',
            'Jumpa lagi pada perjalanan seterusnya! 🚗',
            '*'.static::pic().'* — SEWOLAH',
        ], fn ($l) => $l !== null));
    }

    public static function rejected(Lead $lead): string
    {
        return implode("\n", [
            'Salam sejahtera *'.$lead->full_name.'*,',
            '',
            'Terima kasih atas permohonan tempahan anda ('.$lead->referenceNumber().'). Setelah semakan, kami dukacita memaklumkan bahawa permohonan ini *tidak dapat diteruskan* buat masa ini.',
            '',
            'Sebarang bayaran yang telah dibuat akan dikembalikan sepenuhnya. Terima kasih atas kefahaman anda.',
            '',
            '*'.static::pic().'* — SEWOLAH',
        ]);
    }
}
