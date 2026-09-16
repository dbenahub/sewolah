<h2>Tempahan baharu diterima — SEWOLAH</h2>
<p><strong>Nama:</strong> {{ $lead->full_name }}</p>
<p><strong>Telefon:</strong> {{ $lead->phone }}</p>
<p><strong>Datang Dari:</strong> {{ $lead->origin }}</p>
<p><strong>Airport:</strong> {{ $lead->airport }}</p>
<p><strong>Tarikh/Masa Ketibaan:</strong> {{ $lead->arrival_date }} {{ $lead->arrival_time }}</p>
<p><strong>Tarikh Tamat Sewa:</strong> {{ $lead->end_date }}</p>
<p><strong>Tujuan:</strong> {{ $lead->purpose }}</p>
<p><strong>Kenderaan:</strong> {{ $lead->vehicle_name_snapshot }}</p>
<p><strong>Penumpang:</strong> {{ $lead->passengers }}</p>
<p><strong>Luggage:</strong> {{ $lead->luggage }}</p>
<p><strong>Destinasi:</strong> {{ $lead->destination }}</p>
<p><strong>Catatan:</strong> {{ $lead->notes ?: '-' }}</p>
<p>Sila log masuk ke Admin Panel untuk urus tempahan ini.</p>
