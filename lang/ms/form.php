<?php

return [
    'meta_title' => 'Borang Permohonan Tempahan | SEWOLAH',
    'meta_description' => 'Mohon tempahan kereta sewa SEWOLAH di seluruh Semenanjung Malaysia. Setiap permohonan disemak untuk kelayakan dan availability kenderaan.',

    'eyebrow' => 'BORANG PERMOHONAN TEMPAHAN',
    'title' => 'Mohon Tempahan Kenderaan',
    'intro' => 'Lengkapkan permohonan di bawah. Team SEWOLAH akan menyemak kelayakan anda serta availability kenderaan, kemudian menghubungi anda untuk proses saringan seterusnya.',

    'aside' => [
        'title' => 'Sebelum anda mula',
        'points' => [
            ['title' => 'Melalui permohonan sahaja', 'desc' => 'Penghantaran borang ini bukan pengesahan tempahan. Setiap permohonan disemak secara individu.'],
            ['title' => 'Minimum :days hari bekerja', 'desc' => 'Tarikh ambil kenderaan mesti sekurang-kurangnya :days hari bekerja dari hari ini. Tiada tempahan last minute.'],
            ['title' => 'Tertakluk kepada availability', 'desc' => 'Model kenderaan disahkan berdasarkan availability semasa dalam rangkaian kami.'],
            ['title' => 'Saringan & verifikasi', 'desc' => 'Dokumen seperti kad pengenalan / pasport dan lesen memandu yang sah akan disemak sebelum tempahan disahkan.'],
        ],
        'next_title' => 'Selepas anda hantar',
        'next' => [
            'Notifikasi dihantar terus kepada team SEWOLAH.',
            'Anda menerima e-mel pengesahan penerimaan permohonan.',
            'Anda dibawa ke WhatsApp untuk menghubungi PIC kami.',
            'Team kami menghubungi anda untuk saringan dan sebut harga.',
        ],
        'earliest' => 'Tarikh ambil paling awal hari ini',
    ],

    'step_label' => 'LANGKAH :step DARI 3',
    'steps' => ['Kategori', 'Butiran Sewaan', 'Maklumat Pemohon'],

    'step1' => [
        'title' => 'Siapakah anda?',
        'subtitle' => 'Pilih kategori yang paling tepat. Ini membantu kami menyemak permohonan anda dengan betul.',
    ],

    'categories' => [
        'individual' => ['title' => 'Individu & Keluarga', 'desc' => 'Urusan peribadi, percutian atau perjalanan bersama keluarga.'],
        'corporate' => ['title' => 'Korporat & Syarikat', 'desc' => 'Eksekutif, tetamu syarikat, roadshow atau urusan perniagaan.'],
        'outstation' => ['title' => 'Outstation & Lapangan Terbang', 'desc' => 'Datang dari negeri lain atau luar negara melalui KLIA, KLIA2 atau lapangan terbang lain.'],
        'event' => ['title' => 'Majlis & Acara Khas', 'desc' => 'Perkahwinan, tetamu VIP, acara korporat atau majlis keraian.'],
        'long_term' => ['title' => 'Sewaan Jangka Panjang', 'desc' => 'Sewaan bulanan atau lebih lama untuk individu atau syarikat.'],
    ],

    'step2' => [
        'title' => 'Butiran sewaan',
        'subtitle' => 'Beritahu kami di mana dan bila anda memerlukan kenderaan.',
    ],

    'step3' => [
        'title' => 'Maklumat pemohon',
        'subtitle' => 'Maklumat ini digunakan untuk menghubungi anda dan menjalankan proses saringan.',
    ],

    'fields' => [
        'company_name' => 'Nama Syarikat / Organisasi',
        'company_name_ph' => 'Contoh: ABC Holdings Sdn Bhd',
        'purpose' => 'Tujuan Sewaan',
        'pickup_state' => 'Negeri Ambil Kenderaan',
        'pickup_location' => 'Lokasi / Kawasan Ambil',
        'pickup_location_ph' => 'Contoh: KLIA, Bangsar, Pusat Bandar Ipoh',
        'return_location' => 'Lokasi Pulang Kenderaan (jika berbeza)',
        'return_location_ph' => 'Kosongkan jika sama dengan lokasi ambil',
        'pickup_date' => 'Tarikh Ambil',
        'pickup_time' => 'Masa Ambil',
        'return_date' => 'Tarikh Pulang',
        'earliest_hint' => 'Paling awal: :date (:days hari bekerja dari hari ini).',
        'vehicle' => 'Kenderaan Pilihan',
        'vehicle_any' => 'Tiada pilihan khusus — cadangkan untuk saya',
        'vehicle_other' => 'Lain-lain (nyatakan model)',
        'other_vehicle' => 'Model / Jenis Kenderaan',
        'other_vehicle_ph' => 'Contoh: Mercedes-Benz E-Class, Honda Accord',
        'passengers' => 'Bilangan Penumpang',
        'passengers_ph' => 'Contoh: 4',
        'full_name' => 'Nama Penuh (seperti dalam IC / Pasport)',
        'full_name_ph' => 'Nama penuh',
        'phone' => 'No. Telefon / WhatsApp',
        'phone_ph' => 'Contoh: 0123456789',
        'email' => 'Alamat E-mel',
        'email_ph' => 'nama@email.com',
        'driver_license' => 'Lesen Memandu',
        'notes' => 'Maklumat Tambahan',
        'notes_ph' => 'Contoh: jadual perjalanan, keperluan kerusi kanak-kanak, bilangan bagasi',
        'consent' => 'Saya faham bahawa permohonan ini tertakluk kepada saringan kelayakan dan availability kenderaan, tarikh ambil mesti sekurang-kurangnya :days hari bekerja dari hari ini, dan saya bersetuju dihubungi oleh team SEWOLAH. Saya telah membaca',
        'and' => 'dan',
    ],

    'purpose_options' => [
        'Urusan Peribadi / Keluarga',
        'Percutian',
        'Urusan Kerja / Perniagaan',
        'Tetamu Korporat / VIP',
        'Majlis / Acara',
        'Kenderaan Ganti Sementara',
        'Lain-lain',
    ],

    'license_options' => [
        'malaysia' => 'Lesen memandu Malaysia (CDL) yang sah',
        'international' => 'Lesen asing / Lesen Memandu Antarabangsa (IDP)',
    ],

    'states' => [
        'Perlis', 'Kedah', 'Pulau Pinang', 'Perak', 'Selangor', 'W.P. Kuala Lumpur', 'W.P. Putrajaya',
        'Negeri Sembilan', 'Melaka', 'Johor', 'Pahang', 'Terengganu', 'Kelantan',
    ],

    'select' => 'Pilih',
    'next' => 'Seterusnya',
    'back' => 'Kembali',
    'submit' => 'Hantar Permohonan',
    'submitting' => 'Menghantar...',
    'disclaimer' => 'Penghantaran borang bukan pengesahan tempahan. Tempahan hanya sah selepas saringan selesai dan pengesahan bertulis diberikan oleh team SEWOLAH.',

    'errors' => [
        'required' => 'Sila lengkapkan maklumat ini.',
        'category' => 'Sila pilih satu kategori.',
        'email' => 'Sila masukkan alamat e-mel yang sah.',
        'phone' => 'Sila masukkan nombor telefon Malaysia yang sah (contoh: 0123456789).',
        'min_date' => 'Tarikh ambil paling awal ialah :date. Kami tidak menerima tempahan last minute.',
        'return_date' => 'Tarikh pulang mesti sama atau selepas tarikh ambil.',
        'passengers' => 'Bilangan penumpang mesti antara 1 hingga 50.',
        'consent' => 'Sila tandakan persetujuan untuk meneruskan.',
        'date' => 'Sila masukkan tarikh yang sah.',
    ],

    'success' => [
        'eyebrow' => 'PERMOHONAN DITERIMA',
        'title' => 'Terima kasih, :name.',
        'copy' => 'Permohonan anda telah dihantar kepada team SEWOLAH dan e-mel pengesahan telah dihantar ke :email. Kami akan menyemak kelayakan dan availability kenderaan sebelum menghubungi anda.',
        'redirect' => 'Anda akan dibawa ke WhatsApp untuk menghubungi PIC kami dalam beberapa saat...',
        'cta' => 'Buka WhatsApp Sekarang',
        'ref' => 'No. Rujukan',
    ],

    'whatsapp_message' => "Salam SEWOLAH, saya telah menghantar permohonan tempahan melalui borang di website.\n\nNo. Rujukan: :ref\nNama: :name\nTelefon: :phone\nE-mel: :email\nKategori: :category\nSyarikat: :company\nTujuan: :purpose\nNegeri Ambil: :state\nLokasi Ambil: :pickup_location\nLokasi Pulang: :return_location\nTarikh/Masa Ambil: :pickup\nTarikh Pulang: :return_date\nKenderaan Pilihan: :vehicle\nPenumpang: :passengers\nLesen: :license\nCatatan: :notes\n\nMohon team SEWOLAH semak kelayakan dan availability untuk permohonan saya. Terima kasih.",
];
