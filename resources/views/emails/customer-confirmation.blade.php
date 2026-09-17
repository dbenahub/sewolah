@php
    $isEn = $lead->locale === 'en';
@endphp
<!DOCTYPE html>
<html lang="{{ $lead->locale }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SEWOLAH</title>
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
              <p style="margin:0 0 6px;font-size:20px;font-weight:800;color:#111111;">
                {{ $isEn ? 'Thank You, '.$lead->full_name.'!' : 'Terima Kasih, '.$lead->full_name.'!' }}
              </p>
              <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#555555;">
                {{ $isEn
                    ? 'We have received your booking enquiry. Our team will check vehicle availability and follow up with you shortly. For a faster response, tap the WhatsApp button below to chat with our team directly.'
                    : 'Borang tempahan anda telah kami terima. Team SEWOLAH akan semak availability kenderaan dan hubungi anda tidak lama lagi. Untuk respons lebih pantas, tekan butang WhatsApp di bawah untuk berbual terus dengan team kami.' }}
              </p>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
                <tr>
                  <td align="center" style="background-color:#25D366;border-radius:10px;">
                    <a href="{{ $whatsappUrl }}" style="display:block;padding:14px 20px;font-size:14px;font-weight:800;color:#ffffff;text-decoration:none;">
                      {{ $isEn ? 'CHAT ON WHATSAPP' : 'CHAT DI WHATSAPP' }}
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 10px;font-size:13px;font-weight:800;color:#111111;text-transform:uppercase;letter-spacing:0.5px;">
                {{ $isEn ? 'Your Booking Details' : 'Detail Tempahan Anda' }}
              </p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13.5px;color:#333333;border-collapse:collapse;">
                @php
                    $rows = [
                        ($isEn ? 'Coming From' : 'Datang Dari') => $lead->origin,
                        'Airport' => $lead->airport,
                        ($isEn ? 'Arrival Date/Time' : 'Tarikh/Masa Ketibaan') => trim(optional($lead->arrival_date)->format('d/m/Y').' '.$lead->arrival_time),
                        ($isEn ? 'Rental End Date' : 'Tarikh Tamat Sewa') => optional($lead->end_date)->format('d/m/Y'),
                        ($isEn ? 'Purpose' : 'Tujuan') => $lead->purpose,
                        ($isEn ? 'Preferred Vehicle' : 'Kenderaan Pilihan') => $lead->vehicle_name_snapshot,
                        ($isEn ? 'Passengers' : 'Jumlah Penumpang') => $lead->passengers,
                        ($isEn ? 'Main Destination' : 'Lokasi Utama') => $lead->destination,
                    ];
                @endphp
                @foreach($rows as $label => $value)
                  <tr>
                    <td style="padding:6px 0;border-bottom:1px solid #f0f0f0;color:#888888;width:42%;">{{ $label }}</td>
                    <td style="padding:6px 0;border-bottom:1px solid #f0f0f0;font-weight:600;">{{ $value ?: '-' }}</td>
                  </tr>
                @endforeach
              </table>

              <p style="margin:22px 0 0;font-size:12px;line-height:1.6;color:#999999;">
                {{ $isEn
                    ? 'All vehicle options are subject to availability and confirmation by the SEWOLAH team.'
                    : 'Semua pilihan kenderaan tertakluk kepada availability dan pengesahan oleh team SEWOLAH.' }}
              </p>
            </td>
          </tr>
          <tr>
            <td style="background-color:#f5f5f5;padding:16px 28px;text-align:center;">
              <p style="margin:0;font-size:11px;color:#aaaaaa;">© SEWOLAH — Rent With Confidence. Kuala Lumpur &amp; Selangor.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
