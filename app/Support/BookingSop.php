<?php

namespace App\Support;

use App\Models\Lead;

/**
 * Booking workflow stages and the SOP checklist for every phase,
 * based on the GMHUB / GoGreenMatrix operating notes (NOTA 98–129).
 */
class BookingSop
{
    /** Workflow stages in order. */
    public const STAGES = [
        'permohonan' => 'Permohonan baharu',
        'tawaran' => 'Tawaran dihantar',
        'kelayakan' => 'Menunggu dokumen',
        'semakan' => 'Semakan kelayakan',
        'bayaran' => 'Menunggu bayaran',
        'disahkan' => 'Tempahan disahkan',
        'diserah' => 'Kereta diserah',
        'dipulang' => 'Kereta dipulang',
        'selesai' => 'Selesai',
        'batal' => 'Dibatalkan',
        'ditolak' => 'Tidak lulus',
    ];

    /** Legacy status (dashboard) for each stage. */
    public const STATUS_FOR_STAGE = [
        'permohonan' => 'baru',
        'tawaran' => 'quotation_dihantar',
        'kelayakan' => 'dihubungi',
        'semakan' => 'dihubungi',
        'bayaran' => 'dihubungi',
        'disahkan' => 'disahkan',
        'diserah' => 'disahkan',
        'dipulang' => 'disahkan',
        'selesai' => 'disahkan',
        'batal' => 'batal',
        'ditolak' => 'batal',
    ];

    /**
     * Phase => [label, first stage at which the phase becomes active, items].
     * Item: key => [label, auto (set by the system), source].
     */
    public const PHASES = [
        'tempahan' => ['Tempahan & Tawaran', 'permohonan', [
            'semak_permohonan' => ['Semak butiran permohonan (tarikh, lokasi, kenderaan, kategori)', false, 'SOP SEWOLAH'],
            'semak_kekosongan' => ['Sahkan kereta available dengan owner pada tarikh tersebut', false, 'NOTA 126'],
            'hantar_tawaran' => ['Hantar tawaran harga (harga asal & harga booking) melalui WhatsApp', true, 'Template SEWOLAH'],
            'pelanggan_setuju' => ['Pelanggan bersetuju dengan harga', true, 'Template SEWOLAH'],
        ]],
        'kelayakan' => ['Semakan Kelayakan', 'kelayakan', [
            'hantar_pautan' => ['Hantar pautan borang kelayakan kepada pelanggan', true, 'SOP SEWOLAH'],
            'dokumen_diterima' => ['IC, lesen & bil utiliti diterima', true, 'NOTA 129'],
            'semak_ic_lesen' => ['IC & lesen jelas, nama sepadan, lesen masih sah', false, 'NOTIS SOP 2 Jun'],
            'sahkan_telefon' => ['Nombor telefon disahkan (Truecaller / GetContact)', false, 'NOTA 129'],
            'semak_alamat' => ['Alamat pada bil utiliti sepadan dengan alamat semasa', false, 'NOTA 129'],
            'semak_kerja' => ['Tempat kerja / sumber pendapatan disemak', false, 'NOTA 129'],
            'semak_saringan' => ['Semakan saringan (PSS / rekod) dibuat — status sebenar direkod', false, 'NOTA 121'],
            'semak_waris' => ['Waris terdekat boleh dihubungi', false, 'NOTA 129'],
        ]],
        'pengesahan' => ['Pengesahan Tempahan', 'semakan', [
            'terma_diterima' => ['Pelanggan menerima Terma & Syarat (tandatangan digital)', true, 'SOP SEWOLAH'],
            'kelayakan_lulus' => ['Kelayakan diluluskan oleh PIC', true, 'NOTA 121'],
            'sahkan_semula_owner' => ['Kekosongan kereta disahkan semula dengan owner', false, 'NOTA 126'],
            'jumlah_dimaklumkan' => ['Jumlah sewa + deposit dimaklumkan kepada pelanggan', true, 'NOTA 126'],
        ]],
        'bayaran' => ['Bayaran', 'bayaran', [
            'resit_diterima' => ['Resit bayaran diterima', true, 'SOP SEWOLAH'],
            'bayaran_disahkan' => ['Bayaran disahkan masuk akaun (sewa + deposit penuh)', true, 'NOTA 129'],
            'invois_dihantar' => ['Invois / resit rasmi dihantar kepada pelanggan', false, 'NOTA 126'],
            'mesej_pengesahan' => ['Mesej pengesahan tempahan dihantar kepada pelanggan', true, 'Template SEWOLAH'],
        ]],
        'serahan' => ['Serah Kereta', 'disahkan', [
            'kenderaan_sedia' => ['Kereta bersih, berservis, cukai jalan & insurans sah, GPS aktif', false, 'NOTA 103 / 129'],
            'mesej_sebelum_serahan' => ['WhatsApp pelanggan 3–6 jam sebelum serahan (sahkan masa & lokasi)', true, 'NOTA 102'],
            'minyak_penuh' => ['Tangki PENUH — gambar meter minyak & odometer (cap masa)', false, 'NOTA 117 / 127'],
            'dokumen_asal' => ['IC / pasport & lesen ASAL disemak — orang yang sama dengan tempahan', false, 'NOTIS SOP 2 Jun'],
            'video_serahan' => ['Video pemeriksaan keliling kereta bersama pelanggan', false, 'NOTA 102'],
            'terangkan_terma' => ['Terangkan T&C: penalti, minyak Full to Full, had mileage, caj', false, 'NOTA 102 / 128'],
            'vua_ditandatangani' => ['Perjanjian (VUA) ditandatangani — Remark: lokasi, had mileage, caj/km', false, 'NOTIS SOP 2 Jun'],
            'gambar_rekod' => ['Gambar rekod serahan bersama pelanggan', false, 'NOTA 102'],
            'tiada_harga' => ['Tiada perbincangan harga / komisen dengan pelanggan di lokasi', false, 'NOTA 126'],
        ]],
        'pulangan' => ['Pulang Kereta', 'diserah', [
            'sahkan_masa_pulang' => ['Sahkan masa & lokasi pulang 3–6 jam awal, PIC bersedia', false, 'NOTA 102'],
            'video_pulangan' => ['Video pemeriksaan bersama semasa pulangan', false, 'NOTA 102'],
            'semak_minyak_mileage' => ['Minyak & mileage disemak berbanding semasa serahan', false, 'NOTA 117'],
            'semak_saman' => ['Saman disemak (saman lewat = invois berasingan)', false, 'NOTA 109'],
            'deposit_dipulangkan' => ['Deposit dipulangkan dalam tempoh 14 hari', true, 'NOTA 109'],
            'mesej_terima_kasih' => ['Mesej terima kasih & jemput ulasan', true, 'NOTA 102'],
        ]],
    ];

    public static function stageIndex(?string $stage): int
    {
        $order = array_keys(self::STAGES);
        $i = array_search($stage, $order, true);

        return $i === false ? 0 : $i;
    }

    public static function stageLabel(?string $stage): string
    {
        return self::STAGES[$stage] ?? ucfirst((string) $stage);
    }

    public static function isClosed(?string $stage): bool
    {
        return in_array($stage, ['batal', 'ditolak'], true);
    }

    public static function itemLabel(string $key): string
    {
        foreach (self::PHASES as [$label, $from, $items]) {
            if (isset($items[$key])) {
                return $items[$key][0];
            }
        }

        return $key;
    }

    public static function phaseActive(string $phase, ?string $stage): bool
    {
        if (self::isClosed($stage)) {
            return false;
        }

        return self::stageIndex($stage) >= self::stageIndex(self::PHASES[$phase][1]);
    }

    /**
     * Compliance summary for a lead.
     *
     * @return array{phases: array, done: int, total: int, percent: int}
     */
    public static function compliance(Lead $lead): array
    {
        $checks = (array) ($lead->sop_checks ?? []);
        $phases = [];
        $done = 0;
        $total = 0;
        $closed = self::isClosed($lead->stage);

        foreach (self::PHASES as $key => [$label, $from, $items]) {
            $active = $closed
                ? collect($items)->keys()->contains(fn ($k) => isset($checks[$k]))
                : self::phaseActive($key, $lead->stage);
            $phaseDone = collect($items)->keys()->filter(fn ($k) => isset($checks[$k]))->count();
            $phaseTotal = count($items);
            $phases[$key] = [
                'label' => $label,
                'active' => $active,
                'done' => $phaseDone,
                'total' => $phaseTotal,
                'percent' => (int) round($phaseDone / max(1, $phaseTotal) * 100),
                'items' => $items,
            ];
            if ($active) {
                $done += $phaseDone;
                $total += $phaseTotal;
            }
        }

        return [
            'phases' => $phases,
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];
    }
}
