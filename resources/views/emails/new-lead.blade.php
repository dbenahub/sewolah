@php
    $customerWaNumber = preg_replace('/[^0-9]/', '', $lead->phone);
    if (str_starts_with($customerWaNumber, '0')) {
        $customerWaNumber = '6'.$customerWaNumber;
    }
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SEWOLAH — Tempahan Baharu</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial, Helvetica, sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #eaeaea;">
          <tr>
            <td style="background-color:#000000;padding:22px 28px;">
              <span style="font-size:20px;font-weight:800;color:#ffffff;letter-spacing:0.5px;">SEWO<span style="color:#E31E24;">LAH</span></span>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <p style="margin:0 0 4px;font-size:12px;font-weight:800;color:#E31E24;letter-spacing:0.5px;text-transform:uppercase;">{{ $lead->isGeneral() ? 'Permohonan Baharu · '.$lead->customerCategoryLabel('ms') : 'Tempahan Baharu · Outstation' }}</p>
              <p style="margin:0 0 20px;font-size:20px;font-weight:800;color:#111111;">{{ $lead->full_name }}</p>

              @include('emails.partials.quote-table')

              <p style="margin:0 0 10px;font-size:12px;font-weight:800;color:#111111;letter-spacing:0.4px;text-transform:uppercase;">Tindakan PIC (ikut turutan SOP)</p>
              @include('emails.partials.button', [
                'href' => \App\Http\Controllers\PicActionController::signedUrl($lead, 'quote'),
                'label' => $lead->hasQuote() ? '① WHATSAPP PELANGGAN — SAHKAN AVAILABLE & HARGA' : '① WHATSAPP PELANGGAN — SAHKAN PERMOHONAN',
                'bg' => '#1FAF54',
              ])
              @include('emails.partials.button', [
                'href' => \App\Http\Controllers\PicActionController::signedUrl($lead, 'verification'),
                'label' => '② PELANGGAN SETUJU — HANTAR BORANG KELAYAKAN',
                'bg' => '#111111',
              ])
              @include('emails.partials.button', [
                'href' => route('admin.bookings.manage', $lead),
                'label' => 'URUS TEMPAHAN & SEMAK SOP DI ADMIN',
                'bg' => '#E31E24',
                'mb' => 8,
              ])
              <p style="margin:0 0 22px;font-size:12px;line-height:1.55;color:#888888;">Butang ① & ② membuka WhatsApp dengan mesej siap dan merekod langkah SOP secara automatik.@unless($lead->hasQuote()) Pelanggan belum memilih model tertentu — pilih kereta di Admin untuk mengira harga.@endunless Hubungi terus: <a href="https://wa.me/{{ $customerWaNumber }}" style="color:#1FAF54;font-weight:700;text-decoration:none;">wa.me/{{ $customerWaNumber }}</a></p>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13.5px;color:#333333;border-collapse:collapse;">
                @php
                    $time = $lead->arrival_time ? substr((string) $lead->arrival_time, 0, 5) : '';
                    if ($lead->isGeneral()) {
                        $rows = [
                            'No. Rujukan' => $lead->referenceNumber(),
                            'Sumber' => $lead->sourceLabel(),
                            'Kategori' => $lead->customerCategoryLabel('ms'),
                            'Nama' => $lead->full_name,
                            'Syarikat' => $lead->company_name ?: '-',
                            'Telefon' => $lead->phone,
                            'Email' => $lead->email ?: '-',
                            'Lesen' => $lead->driver_license ? trans('form.license_options.'.$lead->driver_license, [], 'ms') : '-',
                            'Tujuan' => $lead->purpose,
                            'Negeri Ambil' => $lead->pickup_state,
                            'Lokasi Ambil' => $lead->pickup_location,
                            'Lokasi Pulang' => $lead->return_location ?: 'Sama seperti lokasi ambil',
                            'Tarikh/Masa Ambil' => trim(optional($lead->arrival_date)->format('d/m/Y').' '.$time),
                            'Tarikh Pulang' => optional($lead->end_date)->format('d/m/Y'),
                            'Kenderaan' => $lead->vehicle_name_snapshot,
                            'Penumpang' => $lead->passengers,
                            'Catatan' => $lead->notes ?: '-',
                        ];
                    } else {
                        $rows = [
                            'No. Rujukan' => $lead->referenceNumber(),
                            'Sumber' => $lead->sourceLabel(),
                            'Nama' => $lead->full_name,
                            'Telefon' => $lead->phone,
                            'Email' => $lead->email ?: '-',
                            'Datang Dari' => $lead->origin,
                            'Airport' => $lead->airport,
                            'Tarikh/Masa Ketibaan' => trim(optional($lead->arrival_date)->format('d/m/Y').' '.$time),
                            'Tarikh Tamat Sewa' => optional($lead->end_date)->format('d/m/Y'),
                            'Tujuan' => $lead->purpose,
                            'Kenderaan' => $lead->vehicle_name_snapshot,
                            'Model Lain' => $lead->other_vehicle_model ?: '-',
                            'Penumpang' => $lead->passengers,
                            'Luggage' => $lead->luggage,
                            'Destinasi' => $lead->destination,
                            'Catatan' => $lead->notes ?: '-',
                        ];
                    }
                @endphp
                @foreach($rows as $label => $value)
                  <tr>
                    <td style="padding:6px 0;border-bottom:1px solid #f0f0f0;color:#888888;width:38%;vertical-align:top;">{{ $label }}</td>
                    <td style="padding:6px 0;border-bottom:1px solid #f0f0f0;font-weight:600;">{!! nl2br(e($value)) !!}</td>
                  </tr>
                @endforeach
              </table>

              <p style="margin:22px 0 0;font-size:12.5px;color:#666666;">
                Sila log masuk ke <a href="{{ route('admin.bookings') }}" style="color:#E31E24;font-weight:700;text-decoration:none;">Admin Panel</a> untuk urus tempahan ini.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
