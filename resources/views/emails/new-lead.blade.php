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
              <p style="margin:0 0 4px;font-size:12px;font-weight:800;color:#E31E24;letter-spacing:0.5px;text-transform:uppercase;">Tempahan Baharu</p>
              <p style="margin:0 0 20px;font-size:20px;font-weight:800;color:#111111;">{{ $lead->full_name }}</p>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
                <tr>
                  <td align="center" style="background-color:#25D366;border-radius:10px;">
                    <a href="https://wa.me/{{ $customerWaNumber }}" style="display:block;padding:14px 20px;font-size:14px;font-weight:800;color:#ffffff;text-decoration:none;">
                      HUBUNGI PELANGGAN DI WHATSAPP
                    </a>
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13.5px;color:#333333;border-collapse:collapse;">
                @php
                    $rows = [
                        'Nama' => $lead->full_name,
                        'Telefon' => $lead->phone,
                        'Email' => $lead->email ?: '-',
                        'Datang Dari' => $lead->origin,
                        'Airport' => $lead->airport,
                        'Tarikh/Masa Ketibaan' => trim(optional($lead->arrival_date)->format('d/m/Y').' '.$lead->arrival_time),
                        'Tarikh Tamat Sewa' => optional($lead->end_date)->format('d/m/Y'),
                        'Tujuan' => $lead->purpose,
                        'Kenderaan' => $lead->vehicle_name_snapshot,
                        'Model Lain' => $lead->other_vehicle_model ?: '-',
                        'Penumpang' => $lead->passengers,
                        'Luggage' => $lead->luggage,
                        'Destinasi' => $lead->destination,
                        'Catatan' => $lead->notes ?: '-',
                    ];
                @endphp
                @foreach($rows as $label => $value)
                  <tr>
                    <td style="padding:6px 0;border-bottom:1px solid #f0f0f0;color:#888888;width:38%;vertical-align:top;">{{ $label }}</td>
                    <td style="padding:6px 0;border-bottom:1px solid #f0f0f0;font-weight:600;">{{ $value }}</td>
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
