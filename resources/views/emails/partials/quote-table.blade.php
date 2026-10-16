@php $rm = fn ($v) => \App\Support\Pricing::rm($v); @endphp
@if($lead->hasQuote())
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13.5px;color:#333333;border-collapse:collapse;margin-bottom:20px;border:1px solid #eeeeee;border-radius:10px;">
  <tr><td colspan="2" style="padding:10px 14px;background-color:#111111;color:#ffffff;font-weight:800;font-size:13px;border-radius:10px 10px 0 0;">{{ $lead->vehicleName() }} · {{ $lead->rental_days }} hari</td></tr>
  @if($lead->quoted_list_daily > $lead->quoted_daily)
  <tr><td style="padding:8px 14px;color:#888888;border-bottom:1px solid #f0f0f0;">Harga asal</td><td style="padding:8px 14px;text-align:right;text-decoration:line-through;color:#888888;border-bottom:1px solid #f0f0f0;">{{ $rm($lead->quoted_list_daily) }}/hari</td></tr>
  @endif
  <tr><td style="padding:8px 14px;color:#888888;border-bottom:1px solid #f0f0f0;">Harga booking</td><td style="padding:8px 14px;text-align:right;font-weight:700;border-bottom:1px solid #f0f0f0;">{{ $rm($lead->quoted_daily) }}/hari</td></tr>
  <tr><td style="padding:8px 14px;color:#888888;border-bottom:1px solid #f0f0f0;">Sewa ({{ \App\Support\Pricing::PACKAGE_LABELS[$lead->quote_package] ?? 'Harian' }})</td><td style="padding:8px 14px;text-align:right;font-weight:700;border-bottom:1px solid #f0f0f0;">{{ $rm($lead->quoted_rental_total) }}</td></tr>
  <tr><td style="padding:8px 14px;color:#888888;border-bottom:1px solid #f0f0f0;">Deposit</td><td style="padding:8px 14px;text-align:right;font-weight:700;border-bottom:1px solid #f0f0f0;">{{ $rm($lead->deposit_amount) }}</td></tr>
  @if($lead->delivery_fee)
  <tr><td style="padding:8px 14px;color:#888888;border-bottom:1px solid #f0f0f0;">Penghantaran</td><td style="padding:8px 14px;text-align:right;font-weight:700;border-bottom:1px solid #f0f0f0;">{{ $rm($lead->delivery_fee) }}</td></tr>
  @endif
  <tr><td style="padding:10px 14px;font-weight:800;">Jumlah</td><td style="padding:10px 14px;text-align:right;font-weight:800;font-size:16px;color:#E31E24;">{{ $rm($lead->amountDue()) }}</td></tr>
</table>
@endif
