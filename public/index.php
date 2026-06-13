<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/icons.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Bimbingan Konseling SMP Muhammadiyah Cileungsi untuk konsultasi online, monitoring siswa, laporan, dan komunikasi orang tua.">
    <title>BK SMP Muhammadiyah Cileungsi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_path('css/styles.css') ?>">
</head>
<body>
    <header class="site-header" data-header>
        <nav class="navbar container" aria-label="Navigasi utama">
            <a class="brand" href="#home" aria-label="BK SMP Muhammadiyah Cileungsi">
                <span class="brand-mark">BK</span>
                <span>
                    <strong>SMP Muhammadiyah</strong>
                    <small>Cileungsi</small>
                </span>
            </a>
            <button class="nav-toggle" type="button" data-nav-toggle aria-label="Buka menu">
                <span></span><span></span><span></span>
            </button>
            <div class="nav-menu" data-nav-menu>
                <a href="#home">Home</a>
                <a href="#tentang">Tentang</a>
                <a href="#layanan">Layanan BK</a>
                <a href="#fitur">Fitur</a>
                <a href="#kontak">Kontak</a>
                <a href="/login.php">Login</a>
                <a class="btn btn-primary btn-sm" href="/login.php">Konsultasi Sekarang</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero section" id="home">
            <div class="container hero-grid">
                <div class="hero-copy reveal">
                    <span class="eyebrow">Layanan BK Digital Sekolah</span>
                    <h1>Sistem Informasi Bimbingan Konseling SMP Muhammadiyah Cileungsi</h1>
                    <p>Membantu siswa berkembang lebih baik melalui layanan konseling digital yang aman, nyaman, dan mudah diakses.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="/login.php">Mulai Konsultasi</a>
                        <a class="btn btn-secondary" href="#tentang">Pelajari Lebih Lanjut</a>
                    </div>
                    <div class="trust-strip" aria-label="Keunggulan sistem">
                        <span><?= icon_svg('lock') ?> Data privat</span>
                        <span><?= icon_svg('calendar') ?> Jadwal jelas</span>
                        <span><?= icon_svg('bell') ?> Orang tua terhubung</span>
                    </div>
                </div>
                <div class="hero-visual reveal" style="--delay:120ms">
                    <img src="<?= asset_path('images/hero-konseling.png') ?>" alt="Ilustrasi konseling siswa di ruang BK modern">
                </div>
            </div>
        </section>

        <section class="section about" id="tentang">
            <div class="container split">
                <div class="section-heading reveal">
                    <span class="eyebrow">Tentang Sistem</span>
                    <h2>Komunikasi BK lebih terarah, rapi, dan mudah dipantau.</h2>
                </div>
                <div class="about-copy reveal" style="--delay:100ms">
                    <p>Aplikasi BK ini membantu siswa mengajukan konsultasi, guru BK mengelola layanan, wali kelas memahami kondisi siswa, serta orang tua memantau perkembangan anak secara lebih transparan.</p>
                    <div class="benefit-list">
                        <span><?= icon_svg('check') ?> Konsultasi dan jadwal terdokumentasi</span>
                        <span><?= icon_svg('check') ?> Monitoring perkembangan siswa</span>
                        <span><?= icon_svg('check') ?> Notifikasi sekolah untuk orang tua</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section soft-band" id="layanan">
            <div class="container">
                <div class="section-heading center reveal">
                    <span class="eyebrow">Layanan BK</span>
                    <h2>Fitur utama untuk kebutuhan konseling sekolah</h2>
                </div>
                <div class="feature-grid">
                    <?php
                    $features = [
                        ['message', 'Konsultasi Online', 'Siswa dapat mengirim permintaan konsultasi dengan aman.'],
                        ['calendar', 'Pengajuan Jadwal BK', 'Jadwal sesi tertata dan mudah diverifikasi guru BK.'],
                        ['shield', 'Monitoring Pelanggaran', 'Data kedisiplinan terdokumentasi untuk pembinaan.'],
                        ['history', 'Riwayat Konseling', 'Catatan sesi tersimpan sebagai rekam perkembangan.'],
                        ['bell', 'Notifikasi Orang Tua', 'Informasi penting tersampaikan lebih cepat.'],
                        ['chart', 'Laporan Perkembangan', 'Ringkasan progres siswa siap dianalisis.'],
                        ['users', 'Chat Guru BK', 'Komunikasi terarah antara siswa dan guru BK.'],
                        ['alert', 'Pengaduan Siswa', 'Siswa dapat melapor masalah dengan lebih nyaman.'],
                    ];
                    foreach ($features as $index => $feature): ?>
                        <article class="feature-card reveal" style="--delay:<?= $index * 45 ?>ms">
                            <span class="icon-badge"><?= icon_svg($feature[0]) ?></span>
                            <h3><?= e($feature[1]) ?></h3>
                            <p><?= e($feature[2]) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section" id="fitur">
            <div class="container">
                <div class="section-heading center reveal">
                    <span class="eyebrow">Role Pengguna</span>
                    <h2>Satu sistem untuk siswa, orang tua, guru BK, dan admin</h2>
                </div>
                <div class="role-grid">
                    <?php
                    $roles = [
                        ['Siswa', ['Konsultasi online', 'Melihat riwayat', 'Mengajukan sesi BK']],
                        ['Orang Tua', ['Monitoring perkembangan anak', 'Melihat laporan', 'Mendapat notifikasi']],
                        ['Guru BK', ['Mengelola data konseling', 'Membuat laporan', 'Menjadwalkan sesi']],
                        ['Admin', ['Mengelola seluruh sistem', 'Data pengguna', 'Statistik aplikasi']],
                    ];
                    foreach ($roles as $index => $role): ?>
                        <article class="role-card reveal" style="--delay:<?= $index * 70 ?>ms">
                            <h3><?= e($role[0]) ?></h3>
                            <?php foreach ($role[1] as $item): ?>
                                <p><?= icon_svg('check') ?> <?= e($item) ?></p>
                            <?php endforeach; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="stats-band" aria-label="Statistik layanan BK">
            <div class="container stats-grid">
                <div class="stat-item reveal"><strong data-counter="842">0</strong><span>Siswa aktif</span></div>
                <div class="stat-item reveal"><strong data-counter="1260">0</strong><span>Konsultasi</span></div>
                <div class="stat-item reveal"><strong data-counter="8">0</strong><span>Guru BK aktif</span></div>
                <div class="stat-item reveal"><strong data-counter="96">0</strong><span>% Kepuasan layanan</span></div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-heading center reveal">
                    <span class="eyebrow">Testimoni</span>
                    <h2>Pengalaman pengguna layanan BK digital</h2>
                </div>
                <div class="testimonial-grid">
                    <article class="testimonial-card reveal">
                        <p>"Saya jadi lebih mudah mengajukan konsultasi tanpa harus bingung mencari waktu guru BK."</p>
                        <strong>Faris</strong><span>Siswa Kelas VIII</span>
                    </article>
                    <article class="testimonial-card reveal" style="--delay:90ms">
                        <p>"Laporan perkembangan anak lebih mudah dipantau dan komunikasinya terasa lebih cepat."</p>
                        <strong>Ibu Rina</strong><span>Orang Tua Siswa</span>
                    </article>
                    <article class="testimonial-card reveal" style="--delay:180ms">
                        <p>"Catatan konseling, jadwal, dan laporan menjadi lebih tertata untuk kebutuhan sekolah."</p>
                        <strong>Bu Lestari</strong><span>Guru BK</span>
                    </article>
                </div>
            </div>
        </section>

        <section class="section faq soft-band">
            <div class="container split">
                <div class="section-heading reveal">
                    <span class="eyebrow">FAQ</span>
                    <h2>Pertanyaan yang sering ditanyakan</h2>
                </div>
                <div class="faq-list reveal" style="--delay:100ms">
                    <?php
                    $faqs = [
                        ['Apakah data konseling aman?', 'Ya. Akses data dibatasi berdasarkan role pengguna dan proses login memakai sesi aman serta verifikasi password.'],
                        ['Bagaimana cara konsultasi?', 'Siswa login, memilih menu ajukan konsultasi, mengisi topik dan waktu yang diinginkan, lalu menunggu verifikasi guru BK.'],
                        ['Apakah orang tua bisa memantau?', 'Bisa. Orang tua memiliki dashboard khusus untuk melihat riwayat, laporan, dan notifikasi sekolah.'],
                        ['Apakah bisa diakses lewat HP?', 'Bisa. Tampilan dibuat responsif untuk HP, tablet, laptop, dan desktop.'],
                    ];
                    foreach ($faqs as $faq): ?>
                        <details>
                            <summary><?= e($faq[0]) ?></summary>
                            <p><?= e($faq[1]) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer" id="kontak">
        <div class="container footer-grid">
            <div>
                <a class="brand footer-brand" href="#home">
                    <span class="brand-mark">BK</span>
                    <span><strong>SMP Muhammadiyah</strong><small>Cileungsi</small></span>
                </a>
                <p>Sistem informasi bimbingan konseling untuk lingkungan sekolah yang peduli, tertib, dan mendukung perkembangan siswa.</p>
            </div>
            <div>
                <h3>Kontak</h3>
                <p>Jl. Raya Cileungsi, Bogor, Jawa Barat</p>
                <p>Telepon: (021) 0000-0000</p>
                <p>Email: bk@smpmuh-cileungsi.sch.id</p>
            </div>
            <div>
                <h3>Sosial Media</h3>
                <a href="#">Instagram</a>
                <a href="#">Facebook</a>
                <a href="#">YouTube</a>
            </div>
        </div>
        <div class="container copyright">Copyright &copy; <?= date('Y') ?> SMP Muhammadiyah Cileungsi. All rights reserved.</div>
    </footer>

    <script src="<?= asset_path('js/app.js') ?>" defer></script>
</body>
</html>
