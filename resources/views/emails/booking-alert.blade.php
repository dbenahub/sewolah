@include('emails.partials.frame-top')
<p style="margin:0 0 4px;font-size:12px;font-weight:800;color:#E31E24;letter-spacing:0.5px;text-transform:uppercase;">{{ $type === 'payment' ? 'Resit bayaran diterima' : 'Dokumen kelayakan diterima' }} · {{ $lead->referenceNumber() }}</p>
<p style="margin:0 0 16px;font-size:20px;font-weight:800;color:#111111;">{{ $lead->full_name }}</p>
<p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#444444;">
  @if($type === 'payment')
    Pelanggan telah memuat naik resit bayaran sebanyak <strong>{{ \App\Support\Pricing::rm($lead->payment_amount) }}</strong>{{ $lead->payment_reference ? ' (rujukan '.$lead->payment_reference.')' : '' }}. Sila semak akaun bank dan sahkan bayaran di Admin.
  @else
    Pelanggan telah melengkapkan borang kelayakan, memuat naik IC, lesen & bil utiliti, dan menerima Terma & Syarat. Sila buat semakan SOP kelayakan dan luluskan di Admin.
  @endif
</p>
@include('emails.partials.quote-table')
@include('emails.partials.button', ['href' => route('admin.bookings.manage', $lead), 'label' => $type === 'payment' ? 'SEMAK & SAHKAN BAYARAN' : 'SEMAK DOKUMEN & LULUSKAN', 'bg' => '#111111'])
<p style="margin:14px 0 0;font-size:12px;color:#888888;">Dokumen pelanggan hanya boleh dibuka selepas log masuk ke Admin.</p>
@include('emails.partials.frame-bottom')
