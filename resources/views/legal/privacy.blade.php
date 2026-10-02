<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Polisi Privasi | SEWOLAH</title>
  <meta name="description" content="Polisi privasi SEWOLAH untuk pertanyaan dan tempahan kereta sewa.">
  @vite(['resources/css/app.css'])
</head>
<body class="bg-[#080808] text-white font-sans antialiased">
  <main class="max-w-3xl mx-auto px-6 py-12 md:py-20">
    <a href="{{ route('home') }}" class="text-sm text-white/70 hover:text-white">← Kembali ke SEWOLAH</a>
    <h1 class="mt-8 text-3xl md:text-5xl font-extrabold">Polisi Privasi</h1>
    <p class="mt-3 text-sm text-white/55">Kemas kini: 26 September 2026</p>
    <div class="mt-10 space-y-8 text-[15px] leading-7 text-white/75">
      <section><h2 class="text-xl font-bold text-white">Maklumat yang dikumpulkan</h2><p>Kami mengumpulkan maklumat yang anda masukkan dalam borang, termasuk nama, nombor telefon, e-mel, lokasi asal, butiran perjalanan, pilihan kenderaan dan catatan. Kami juga boleh menyimpan sumber kunjungan seperti UTM, referrer dan fbclid untuk mengukur kempen.</p></section>
      <section><h2 class="text-xl font-bold text-white">Tujuan penggunaan</h2><p>Maklumat digunakan untuk menyemak ketersediaan, mencadangkan kenderaan, menyediakan sebut harga, menghubungi anda melalui WhatsApp atau e-mel, mengurus pertanyaan serta mengukur keberkesanan pemasaran.</p></section>
      <section><h2 class="text-xl font-bold text-white">Pixel dan kuki</h2><p>Laman ini boleh menggunakan Meta Pixel dan teknologi serupa selepas anda memberi kebenaran untuk merekod lawatan, permulaan borang dan penghantaran lead. Anda boleh menolak kuki pemasaran tanpa menghalang penggunaan borang tempahan.</p></section>
      <section><h2 class="text-xl font-bold text-white">Perkongsian dan keselamatan</h2><p>Kami tidak menjual data peribadi. Data boleh diproses oleh penyedia hosting, e-mel, WhatsApp dan platform pengiklanan setakat yang diperlukan untuk menyediakan servis. Kami mengambil langkah munasabah untuk melindungi data, namun tiada penghantaran internet yang bebas risiko sepenuhnya.</p></section>
      <section><h2 class="text-xl font-bold text-white">Hak dan pertanyaan</h2><p>Anda boleh meminta akses, pembetulan atau pemadaman maklumat tertakluk kepada keperluan undang-undang dan rekod perniagaan. Hubungi kami melalui WhatsApp di <a class="text-red-400 underline" href="https://wa.me/{{ $pageSettings->whatsapp_number }}">+{{ $pageSettings->whatsapp_number }}</a> atau e-mel <a class="text-red-400 underline" href="mailto:{{ $pageSettings->admin_notification_email }}">{{ $pageSettings->admin_notification_email }}</a>.</p></section>
    </div>
  </main>
</body>
</html>
