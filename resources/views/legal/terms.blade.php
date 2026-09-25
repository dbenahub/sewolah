<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Terma dan Syarat | SEWOLAH</title>
  <meta name="description" content="Terma pertanyaan dan tempahan kereta sewa SEWOLAH.">
  @vite(['resources/css/app.css'])
</head>
<body class="bg-[#080808] text-white font-sans antialiased">
  <main class="max-w-3xl mx-auto px-6 py-12 md:py-20">
    <a href="{{ route('landing') }}" class="text-sm text-white/70 hover:text-white">← Kembali ke SEWOLAH</a>
    <h1 class="mt-8 text-3xl md:text-5xl font-extrabold">Terma dan Syarat</h1>
    <p class="mt-3 text-sm text-white/55">Kemas kini: 26 September 2026</p>
    <div class="mt-10 space-y-8 text-[15px] leading-7 text-white/75">
      <section><h2 class="text-xl font-bold text-white">Permintaan semakan</h2><p>Penghantaran borang ialah permintaan untuk semakan, bukan pengesahan tempahan. Ketersediaan, model, harga, deposit, lokasi serahan dan syarat akhir hanya sah selepas disahkan oleh pihak SEWOLAH.</p></section>
      <section><h2 class="text-xl font-bold text-white">Kelayakan penyewa</h2><p>Penyewa perlu memberikan maklumat yang tepat serta memenuhi keperluan lesen, umur, identiti dan dokumen yang ditetapkan untuk kenderaan berkenaan.</p></section>
      <section><h2 class="text-xl font-bold text-white">Bayaran dan deposit</h2><p>Kadar sewa, deposit keselamatan, caj tambahan, kaedah bayaran dan polisi pembatalan akan dinyatakan dalam sebut harga atau pengesahan tempahan. Jangan membuat bayaran kepada akaun yang tidak disahkan oleh SEWOLAH.</p></section>
      <section><h2 class="text-xl font-bold text-white">Penggunaan kenderaan</h2><p>Pelanggan wajib mematuhi undang-undang jalan raya dan syarat penggunaan yang dipersetujui. Saman, tol, kerosakan, kehilangan, bahan api, had perjalanan dan lebihan perlindungan boleh dikenakan mengikut perjanjian sewaan.</p></section>
      <section><h2 class="text-xl font-bold text-white">Perubahan dan pembatalan</h2><p>Perubahan jadual, kelewatan penerbangan atau pembatalan hendaklah dimaklumkan segera. Kelulusan perubahan tertakluk kepada ketersediaan dan syarat tempahan.</p></section>
      <section><h2 class="text-xl font-bold text-white">Pertanyaan</h2><p>Untuk penjelasan sebelum membuat tempahan, hubungi kami melalui WhatsApp di <a class="text-red-400 underline" href="https://wa.me/{{ $pageSettings->whatsapp_number }}">+{{ $pageSettings->whatsapp_number }}</a> atau e-mel <a class="text-red-400 underline" href="mailto:{{ $pageSettings->admin_notification_email }}">{{ $pageSettings->admin_notification_email }}</a>.</p></section>
    </div>
  </main>
</body>
</html>
