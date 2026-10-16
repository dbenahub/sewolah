@php
  $refundDays = \App\Support\Pricing::setting('deposit_refund_days');
  $hourDiv = \App\Support\Pricing::setting('extra_hour_divisor');
  $minDays = \App\Support\BookingPolicy::minWorkingDays();
  $terms = [
    ['Kelayakan penyewa', [
      'Penyewa mesti memiliki MyKad atau pasport yang sah, dan lesen memandu Malaysia yang sah atau International Driving Permit (IDP) yang sah.',
      'Penyewa wajib melengkapkan semakan kelayakan: salinan IC / pasport, lesen memandu, bil utiliti terkini (TNB, air atau internet) dan maklumat waris terdekat.',
      'SEWOLAH berhak membuat semakan identiti, nombor telefon, alamat, tempat kerja dan rekod saringan sebelum meluluskan tempahan. Keputusan kelayakan adalah muktamad.',
      'Hanya penyewa yang berdaftar dalam tempahan dibenarkan memandu kenderaan, kecuali dipersetujui secara bertulis.',
    ]],
    ['Tempahan & bayaran', [
      "Tempahan mesti dibuat sekurang-kurangnya {$minDays} hari bekerja sebelum tarikh ambil. Tempahan saat akhir tidak diterima.",
      'Tempahan hanya disahkan selepas kelayakan diluluskan dan bayaran penuh (sewa + deposit keselamatan + caj penghantaran, jika ada) diterima.',
      'Sewaan bulanan untuk pelanggan baharu: sewa bulan pertama dan deposit bersamaan 1 bulan sewa mesti dijelaskan sebelum kenderaan diserahkan. Tempahan bulanan tidak diluluskan pada hari yang sama.',
      'Lanjutan tempoh sewa mesti dimaklumkan awal dan dibayar terlebih dahulu sebelum diluluskan.',
      "Masa tambahan dikenakan caj sejam bersamaan harga sehari dibahagi {$hourDiv}.",
    ]],
    ['Deposit keselamatan', [
      'Deposit keselamatan ialah wang amanah dan akan dikembalikan sepenuhnya jika tiada tuntutan yang sah.',
      "Deposit dikembalikan dalam tempoh {$refundDays} hari selepas kenderaan dipulangkan dan diperiksa.",
      'Potongan deposit hanya untuk tuntutan sah yang disokong bukti: kerosakan, kekurangan minyak, mileage berlebihan, kebersihan luar biasa atau caj lain yang dipersetujui.',
      'Saman yang diterima selepas tamat sewa (contoh saman AES / JPJ / PDRM) akan dituntut melalui invois berasingan dan tidak dijadikan alasan menahan deposit.',
    ]],
    ['Pembatalan & pemulangan awal', [
      'Pembatalan sekurang-kurangnya 72 jam (3 hari) sebelum masa ambil: bayaran dikembalikan sepenuhnya.',
      'Pembatalan kurang daripada 72 jam sebelum masa ambil: 1 hari sewa akan ditolak; baki sewa dan deposit dikembalikan.',
      'Pemulangan awal semasa tempoh sewa dengan notis kurang daripada 72 jam: tiada pengembalian bagi baki tempoh sewa.',
    ]],
    ['Polisi minyak — Full to Full', [
      'Kenderaan diserahkan dengan tangki minyak penuh dan mesti dipulangkan dengan tangki penuh.',
      'Kekurangan minyak dikenakan caj mengikut kadar per bar yang dimaklumkan semasa serahan, berdasarkan gambar meter minyak sebelum dan selepas.',
      'Jika kenderaan diserahkan tidak penuh, penyewa hanya perlu memulangkan pada paras yang sama; minyak lebihan yang diisi akan dikembalikan nilainya secara adil.',
    ]],
    ['Penggunaan kenderaan', [
      'Lokasi penggunaan yang dibenarkan, had mileage dan caj setiap kilometer tambahan dicatat dalam Perjanjian Penggunaan Kenderaan (VUA) semasa serahan.',
      'Penggunaan yang jauh melebihi anggaran yang dimaklumkan semasa tempahan adalah pelanggaran syarat dan perjanjian boleh ditamatkan serta-merta.',
      'Dilarang sama sekali: menjual, menggadai, memajak semula, menyewakan semula, membuat sambung bayar, menggunakan untuk aktiviti haram, perlumbaan atau membawa keluar dari Semenanjung Malaysia tanpa kebenaran bertulis (termasuk RORO ke Langkawi).',
      'Kenderaan dipasang GPS untuk tujuan keselamatan dan pemantauan sepanjang tempoh sewaan.',
    ]],
    ['Serahan & pemulangan', [
      'Semasa serahan, IC / pasport dan lesen memandu ASAL akan disemak. Kenderaan tidak akan diserahkan jika dokumen asal tidak dapat ditunjukkan atau tidak sepadan.',
      'Pemeriksaan keliling kenderaan dibuat bersama dan dirakam (video & gambar, termasuk meter minyak dan odometer) semasa serahan dan pemulangan.',
      'Penyewa wajib menandatangani Perjanjian Penggunaan Kenderaan (VUA) sebelum kunci diserahkan.',
      'Masa dan lokasi pemulangan perlu disahkan sekurang-kurangnya 3 jam lebih awal.',
    ]],
    ['Kemalangan, kerosakan & saman', [
      'Sebarang kemalangan, kerosakan, kecurian atau saman mesti dilaporkan kepada SEWOLAH dengan segera.',
      'Penyewa bertanggungjawab ke atas saman lalu lintas, tol, parkir dan kerosakan yang berlaku sepanjang tempoh sewaan.',
    ]],
    ['Kegagalan bayaran & pelanggaran', [
      'Penyewa yang gagal membuat bayaran, mempunyai tunggakan atau melanggar perjanjian boleh direkodkan dalam sistem CTOS selepas peringatan diberi.',
      'Jika kenderaan tidak dipulangkan dalam tempoh yang ditetapkan, laporan polis dan tindakan undang-undang akan diambil untuk mendapatkan semula kenderaan serta menuntut kerugian.',
    ]],
    ['Sewaan syarikat', [
      'Salinan geran kenderaan hanya diberi untuk tujuan sewaan, bertanda "UNTUK TUJUAN SEWAAN KENDERAAN SAHAJA". Geran asal tidak diserahkan.',
      'Pelanggan syarikat perlu mengemukakan Letter of Undertaking (LOU) berkepala surat syarikat yang menyatakan No. SSM, PIC, tempoh & tujuan sewaan serta tanggungjawab terhadap saman dan kerosakan.',
    ]],
    ['Privasi & dokumen', [
      'Dokumen yang dimuat naik disimpan secara sulit dan hanya digunakan untuk semakan kelayakan, pengesahan identiti dan urusan tempahan ini, selaras dengan Akta Perlindungan Data Peribadi 2010.',
    ]],
  ];
@endphp
<div class="sw-terms">
  @foreach($terms as $i => [$heading, $items])
    <section class="sw-terms__section">
      <h3>{{ $i + 1 }}. {{ $heading }}</h3>
      <ol>
        @foreach($items as $item)
          <li>{{ $item }}</li>
        @endforeach
      </ol>
    </section>
  @endforeach
</div>
