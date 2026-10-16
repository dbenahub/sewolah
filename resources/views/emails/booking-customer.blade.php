@include('emails.partials.frame-top')
@if($type === 'approved')
  <p style="margin:0 0 4px;font-size:12px;font-weight:800;color:#1FAF54;letter-spacing:0.5px;text-transform:uppercase;">Kelayakan diluluskan</p>
  <p style="margin:0 0 14px;font-size:20px;font-weight:800;color:#111111;">Tahniah, {{ $lead->full_name }}!</p>
  <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#444444;">Semakan kelayakan untuk tempahan <strong>{{ $lead->referenceNumber() }}</strong> telah diluluskan. Sila buat bayaran penuh dan muat naik resit melalui pautan di bawah untuk mengesahkan tempahan anda.</p>
  @include('emails.partials.quote-table')
  @include('emails.partials.button', ['href' => $lead->portalUrl(), 'label' => 'BUAT BAYARAN SEKARANG'])
@else
  <p style="margin:0 0 4px;font-size:12px;font-weight:800;color:#1FAF54;letter-spacing:0.5px;text-transform:uppercase;">Tempahan disahkan</p>
  <p style="margin:0 0 14px;font-size:20px;font-weight:800;color:#111111;">Terima kasih, {{ $lead->full_name }}</p>
  <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#444444;">Bayaran anda telah diterima dan tempahan <strong>{{ $lead->referenceNumber() }}</strong> kini <strong>disahkan</strong>.</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13.5px;color:#333333;margin-bottom:18px;">
    <tr><td style="padding:6px 0;color:#888888;width:38%;">Kereta</td><td style="padding:6px 0;font-weight:700;">{{ $lead->vehicleName() }}</td></tr>
    <tr><td style="padding:6px 0;color:#888888;">Ambil</td><td style="padding:6px 0;font-weight:700;">{{ $lead->pickupLabel() }}</td></tr>
    <tr><td style="padding:6px 0;color:#888888;">Pulang</td><td style="padding:6px 0;font-weight:700;">{{ optional($lead->end_date)->format('d/m/Y') }}</td></tr>
    <tr><td style="padding:6px 0;color:#888888;">Lokasi</td><td style="padding:6px 0;font-weight:700;">{{ $lead->pickupPlace() }}</td></tr>
  </table>
  <p style="margin:0 0 8px;font-size:13px;font-weight:800;color:#111111;">Sila bawa semasa mengambil kereta:</p>
  <p style="margin:0 0 20px;font-size:13.5px;line-height:1.7;color:#444444;">• IC / pasport ASAL<br>• Lesen memandu ASAL yang sah<br>• Kereta diserah dengan tangki penuh — pulangkan penuh (Full to Full)<br>• Staf operasi akan menghubungi anda 3–6 jam sebelum serahan</p>
  @include('emails.partials.button', ['href' => $lead->portalUrl(), 'label' => 'LIHAT STATUS TEMPAHAN', 'bg' => '#111111'])
@endif
<p style="margin:16px 0 0;font-size:12px;color:#888888;">Pautan ini khas untuk tempahan anda — jangan kongsikan kepada orang lain.</p>
@include('emails.partials.frame-bottom')
