<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/DataStore.php';
require_once __DIR__ . '/../app/icons.php';

Auth::requireAuth();

$user = Auth::user();
$role = $user['role'];
$data = DataStore::all();
$users = $data['users'];
$students = $data['students'];
$consultations = $data['consultations'];
$schedules = $data['schedules'];
$notes = $data['notes'];
$violations = $data['violations'];
$notifications = $data['notifications'];
$messages = $data['messages'];

$roleLabels = [
    'siswa' => 'Dashboard Siswa',
    'orang_tua' => 'Dashboard Orang Tua',
    'guru_bk' => 'Dashboard Guru BK',
    'admin' => 'Dashboard Admin',
];

$menus = [
    'siswa' => [
        'overview' => ['chart', 'Dashboard'],
        'konsultasi' => ['message', 'Ajukan Konsultasi'],
        'chat' => ['users', 'Chat Guru BK'],
        'jadwal' => ['calendar', 'Jadwal'],
        'riwayat' => ['history', 'Riwayat'],
        'notifikasi' => ['bell', 'Notifikasi'],
    ],
    'orang_tua' => [
        'overview' => ['chart', 'Dashboard'],
        'monitoring' => ['chart', 'Monitoring Anak'],
        'riwayat' => ['history', 'Riwayat Konseling'],
        'notifikasi' => ['bell', 'Notifikasi'],
        'komunikasi' => ['message', 'Komunikasi BK'],
    ],
    'guru_bk' => [
        'overview' => ['chart', 'Dashboard'],
        'konsultasi' => ['message', 'Kelola Konsultasi'],
        'jadwal' => ['calendar', 'Verifikasi Jadwal'],
        'perkembangan' => ['chart', 'Catatan Perkembangan'],
        'pelanggaran' => ['shield', 'Data Pelanggaran'],
        'laporan' => ['history', 'Laporan PDF'],
    ],
    'admin' => [
        'overview' => ['chart', 'Dashboard'],
        'users' => ['users', 'Kelola User'],
        'siswa' => ['chart', 'Data Siswa'],
        'statistik' => ['history', 'Statistik Sistem'],
        'backup' => ['shield', 'Backup Data'],
    ],
];

$module = $_GET['module'] ?? 'overview';
if (!isset($menus[$role][$module])) {
    $module = 'overview';
}

function user_name(array $users, int $id): string
{
    foreach ($users as $item) {
        if ((int) $item['id'] === $id) {
            return $item['name'];
        }
    }

    return '-';
}

function student_name(array $students, int $id): string
{
    foreach ($students as $item) {
        if ((int) $item['id'] === $id) {
            return $item['name'] . ' - ' . $item['class_name'];
        }
    }

    return '-';
}

function status_badge(string $status): string
{
    return '<span class="status-badge status-' . e($status) . '">' . e(ucfirst(str_replace('_', ' ', $status))) . '</span>';
}

function date_label(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d M Y H:i', strtotime($date));
}

function child_ids_for_parent(int $parentId): array
{
    return array_map(fn ($student) => (int) $student['id'], DataStore::childrenForParent($parentId));
}

function consultation_rows(array $consultations, array $students, ?array $studentIds = null): array
{
    if ($studentIds === null) {
        return $consultations;
    }

    return array_values(array_filter($consultations, fn ($item) => in_array((int) $item['student_id'], $studentIds, true)));
}

$currentStudent = DataStore::findStudentByUser((int) $user['id']);
$studentIds = $role === 'siswa' && $currentStudent ? [(int) $currentStudent['id']] : null;
if ($role === 'orang_tua') {
    $studentIds = child_ids_for_parent((int) $user['id']);
}

$visibleConsultations = consultation_rows($consultations, $students, $studentIds);
$visibleSchedules = $studentIds === null ? $schedules : array_values(array_filter($schedules, fn ($item) => in_array((int) $item['student_id'], $studentIds, true)));
$visibleNotes = $studentIds === null ? $notes : array_values(array_filter($notes, fn ($item) => in_array((int) $item['student_id'], $studentIds, true)));
$visibleViolations = $studentIds === null ? $violations : array_values(array_filter($violations, fn ($item) => in_array((int) $item['student_id'], $studentIds, true)));
$visibleNotifications = array_values(array_filter($notifications, fn ($item) => (int) $item['user_id'] === (int) $user['id']));
$visibleMessages = array_values(array_filter($messages, fn ($item) => (int) $item['from_user_id'] === (int) $user['id'] || (int) $item['to_user_id'] === (int) $user['id']));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard Sistem Informasi BK SMP Muhammadiyah Cileungsi.">
    <title><?= e($roleLabels[$role] ?? 'Dashboard') ?> - BK SMP Muhammadiyah Cileungsi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_path('css/styles.css') ?>">
</head>
<body class="dashboard-body">
    <aside class="sidebar">
        <a class="brand" href="/">
            <span class="brand-mark">BK</span>
            <span><strong>SMP Muhammadiyah</strong><small>Cileungsi</small></span>
        </a>
        <nav class="side-nav" aria-label="Menu dashboard">
            <?php foreach ($menus[$role] as $key => $item): ?>
                <a class="<?= $module === $key ? 'active' : '' ?>" href="/dashboard.php?module=<?= e($key) ?>">
                    <?= icon_svg($item[0]) ?> <?= e($item[1]) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <a class="logout-link" href="/logout.php">Logout</a>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <span class="eyebrow">Sistem BK Digital</span>
                <h1><?= e($menus[$role][$module][1]) ?></h1>
            </div>
            <div class="user-chip">
                <span><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span>
                <div>
                    <strong><?= e($user['name']) ?></strong>
                    <small><?= e(str_replace('_', ' ', $role)) ?></small>
                </div>
            </div>
        </header>

        <?php if ($message = flash('success')): ?>
            <div class="success-box"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($message = flash('error')): ?>
            <div class="alert-box"><?= e($message) ?></div>
        <?php endif; ?>

        <section class="dashboard-hero">
            <div>
                <h2><?= e($roleLabels[$role] ?? 'Dashboard') ?></h2>
                <p>Semua menu di halaman ini sudah bisa dipakai sesuai hak akses pengguna.</p>
            </div>
            <button class="mode-toggle" type="button" data-theme-toggle aria-label="Ubah tema">Mode</button>
        </section>

        <?php if ($module === 'overview'): ?>
            <section class="metric-grid">
                <article class="metric-card"><strong><?= count($visibleConsultations) ?></strong><span>Konsultasi</span></article>
                <article class="metric-card"><strong><?= count($visibleSchedules) ?></strong><span>Jadwal</span></article>
                <article class="metric-card"><strong><?= count($visibleNotifications) ?></strong><span>Notifikasi</span></article>
                <article class="metric-card"><strong><?= count($visibleMessages) ?></strong><span>Pesan</span></article>
            </section>
            <section class="dashboard-grid">
                <?php foreach ($menus[$role] as $key => $item): ?>
                    <?php if ($key === 'overview') continue; ?>
                    <a class="dashboard-card action-card" href="/dashboard.php?module=<?= e($key) ?>">
                        <span class="icon-badge"><?= icon_svg($item[0]) ?></span>
                        <h3><?= e($item[1]) ?></h3>
                        <p>Buka modul <?= e(strtolower($item[1])) ?>.</p>
                    </a>
                <?php endforeach; ?>
            </section>

        <?php elseif ($role === 'siswa' && $module === 'konsultasi'): ?>
            <section class="panel">
                <div class="panel-header"><h2>Ajukan Konsultasi Baru</h2><span>Privat</span></div>
                <form class="compact-form two-column-form" action="/action.php" method="post" data-validate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="request_consultation">
                    <input type="hidden" name="module" value="konsultasi">
                    <label>Topik <input type="text" name="topic" placeholder="Contoh: kesulitan belajar matematika" required minlength="4"></label>
                    <label>Tanggal yang Diinginkan <input type="date" name="preferred_date"></label>
                    <label class="span-2">Pesan <textarea name="message" rows="5" placeholder="Ceritakan kebutuhan konsultasi secara singkat" required minlength="10"></textarea></label>
                    <button class="btn btn-primary" type="submit">Kirim Pengajuan</button>
                </form>
            </section>

        <?php elseif (($role === 'siswa' && $module === 'chat') || ($role === 'orang_tua' && $module === 'komunikasi')): ?>
            <section class="dashboard-columns">
                <article class="panel">
                    <div class="panel-header"><h2>Ruang Pesan</h2><span><?= count($visibleMessages) ?> pesan</span></div>
                    <div class="chat-list">
                        <?php foreach ($visibleMessages as $item): ?>
                            <div class="chat-item <?= (int) $item['from_user_id'] === (int) $user['id'] ? 'mine' : '' ?>">
                                <strong><?= e(user_name($users, (int) $item['from_user_id'])) ?></strong>
                                <p><?= e($item['body']) ?></p>
                                <small><?= e(date_label($item['created_at'] ?? null)) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <article class="panel">
                    <div class="panel-header"><h2>Kirim Pesan</h2><span>Guru BK</span></div>
                    <form class="compact-form" action="/action.php" method="post" data-validate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="module" value="<?= $role === 'siswa' ? 'chat' : 'komunikasi' ?>">
                        <input type="hidden" name="to_user_id" value="3">
                        <label>Pesan <textarea name="body" rows="5" required minlength="3" placeholder="Tulis pesan untuk guru BK"></textarea></label>
                        <button class="btn btn-primary" type="submit">Kirim Pesan</button>
                    </form>
                </article>
            </section>

        <?php elseif (($role === 'siswa' && $module === 'jadwal') || ($role === 'orang_tua' && $module === 'riwayat') || ($role === 'guru_bk' && $module === 'jadwal')): ?>
            <section class="panel">
                <div class="panel-header"><h2>Jadwal Konseling</h2><span><?= count($visibleSchedules) ?> jadwal</span></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Siswa</th><th>Guru BK</th><th>Waktu</th><th>Status</th><th>Catatan</th></tr></thead>
                        <tbody>
                            <?php foreach ($visibleSchedules as $item): ?>
                                <tr>
                                    <td><?= e(student_name($students, (int) $item['student_id'])) ?></td>
                                    <td><?= e(user_name($users, (int) $item['counselor_id'])) ?></td>
                                    <td><?= e(date_label($item['schedule_at'])) ?></td>
                                    <td><?= status_badge($item['status']) ?></td>
                                    <td><?= e($item['notes'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($role === 'siswa' && $module === 'riwayat'): ?>
            <section class="panel">
                <div class="panel-header"><h2>Riwayat Konseling</h2><span><?= count($visibleConsultations) ?> data</span></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Topik</th><th>Tanggal Diajukan</th><th>Preferensi</th><th>Status</th><th>Respon Guru BK</th></tr></thead>
                        <tbody>
                            <?php foreach ($visibleConsultations as $item): ?>
                                <tr>
                                    <td><?= e($item['topic']) ?></td>
                                    <td><?= e(date_label($item['created_at'] ?? null)) ?></td>
                                    <td><?= e($item['preferred_date'] ?: '-') ?></td>
                                    <td><?= status_badge($item['status']) ?></td>
                                    <td><?= e($item['response'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif (($role === 'siswa' || $role === 'orang_tua') && $module === 'notifikasi'): ?>
            <section class="panel">
                <div class="panel-header"><h2>Notifikasi</h2><span><?= count($visibleNotifications) ?> info</span></div>
                <div class="timeline">
                    <?php foreach ($visibleNotifications as $item): ?>
                        <div><strong><?= e($item['title']) ?></strong><p><?= e($item['body']) ?></p><small><?= e(date_label($item['created_at'] ?? null)) ?></small></div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php elseif ($role === 'orang_tua' && $module === 'monitoring'): ?>
            <section class="dashboard-grid">
                <?php foreach (DataStore::childrenForParent((int) $user['id']) as $child): ?>
                    <article class="dashboard-card">
                        <span class="icon-badge"><?= icon_svg('users') ?></span>
                        <h3><?= e($child['name']) ?></h3>
                        <p>Kelas <?= e($child['class_name']) ?>, NIS <?= e($child['nis']) ?></p>
                        <div class="mini-stats">
                            <span><?= count(consultation_rows($consultations, $students, [(int) $child['id']])) ?> konsultasi</span>
                            <span><?= count(array_filter($notes, fn ($item) => (int) $item['student_id'] === (int) $child['id'])) ?> catatan</span>
                            <span><?= count(array_filter($violations, fn ($item) => (int) $item['student_id'] === (int) $child['id'])) ?> pelanggaran</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

        <?php elseif ($role === 'guru_bk' && $module === 'konsultasi'): ?>
            <section class="panel">
                <div class="panel-header"><h2>Kelola Konsultasi</h2><span><?= count($consultations) ?> pengajuan</span></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Siswa</th><th>Topik</th><th>Pesan</th><th>Status dan Jadwal</th></tr></thead>
                        <tbody>
                            <?php foreach ($consultations as $item): ?>
                                <tr>
                                    <td><?= e(student_name($students, (int) $item['student_id'])) ?></td>
                                    <td><?= e($item['topic']) ?></td>
                                    <td><?= e($item['message']) ?></td>
                                    <td>
                                        <form class="inline-form" action="/action.php" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="update_consultation">
                                            <input type="hidden" name="consultation_id" value="<?= (int) $item['id'] ?>">
                                            <select name="status">
                                                <?php foreach (['pending', 'approved', 'completed', 'rejected'] as $status): ?>
                                                    <option value="<?= e($status) ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="datetime-local" name="schedule_at">
                                            <textarea name="response" rows="2" placeholder="Respon atau catatan"><?= e($item['response'] ?? '') ?></textarea>
                                            <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($role === 'guru_bk' && $module === 'perkembangan'): ?>
            <section class="dashboard-columns">
                <article class="panel">
                    <div class="panel-header"><h2>Tambah Catatan</h2><span>Perkembangan</span></div>
                    <form class="compact-form" action="/action.php" method="post" data-validate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_note">
                        <label>Siswa <select name="student_id"><?php foreach ($students as $student): ?><option value="<?= (int) $student['id'] ?>"><?= e(student_name($students, (int) $student['id'])) ?></option><?php endforeach; ?></select></label>
                        <label>Judul <input type="text" name="title" required minlength="4"></label>
                        <label>Catatan <textarea name="body" rows="4" required minlength="10"></textarea></label>
                        <button class="btn btn-primary" type="submit">Simpan Catatan</button>
                    </form>
                </article>
                <article class="panel">
                    <div class="panel-header"><h2>Riwayat Catatan</h2><span><?= count($notes) ?> data</span></div>
                    <div class="timeline">
                        <?php foreach ($notes as $item): ?>
                            <div><strong><?= e($item['title']) ?></strong><p><?= e(student_name($students, (int) $item['student_id'])) ?> - <?= e($item['body']) ?></p></div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

        <?php elseif ($role === 'guru_bk' && $module === 'pelanggaran'): ?>
            <section class="dashboard-columns">
                <article class="panel">
                    <div class="panel-header"><h2>Tambah Pelanggaran</h2><span>Pembinaan</span></div>
                    <form class="compact-form" action="/action.php" method="post" data-validate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_violation">
                        <label>Siswa <select name="student_id"><?php foreach ($students as $student): ?><option value="<?= (int) $student['id'] ?>"><?= e(student_name($students, (int) $student['id'])) ?></option><?php endforeach; ?></select></label>
                        <label>Kategori <input type="text" name="category" required minlength="3"></label>
                        <label>Poin <input type="number" name="point" min="0" value="5"></label>
                        <label>Tanggal <input type="date" name="incident_date" value="<?= date('Y-m-d') ?>"></label>
                        <label>Deskripsi <textarea name="description" rows="4" required minlength="6"></textarea></label>
                        <button class="btn btn-primary" type="submit">Simpan Pelanggaran</button>
                    </form>
                </article>
                <article class="panel">
                    <div class="panel-header"><h2>Data Pelanggaran</h2><span><?= count($violations) ?> data</span></div>
                    <div class="timeline">
                        <?php foreach ($violations as $item): ?>
                            <div><strong><?= e($item['category']) ?> - <?= (int) $item['point'] ?> poin</strong><p><?= e(student_name($students, (int) $item['student_id'])) ?>: <?= e($item['description']) ?></p></div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

        <?php elseif ($role === 'guru_bk' && $module === 'laporan'): ?>
            <section class="panel printable-report">
                <div class="panel-header"><h2>Laporan BK</h2><button class="btn btn-primary btn-sm" type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
                <div class="metric-grid">
                    <article class="metric-card"><strong><?= count($students) ?></strong><span>Siswa</span></article>
                    <article class="metric-card"><strong><?= count($consultations) ?></strong><span>Konsultasi</span></article>
                    <article class="metric-card"><strong><?= count($notes) ?></strong><span>Catatan</span></article>
                    <article class="metric-card"><strong><?= count($violations) ?></strong><span>Pelanggaran</span></article>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Siswa</th><th>Konsultasi</th><th>Catatan</th><th>Pelanggaran</th></tr></thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= e(student_name($students, (int) $student['id'])) ?></td>
                                    <td><?= count(consultation_rows($consultations, $students, [(int) $student['id']])) ?></td>
                                    <td><?= count(array_filter($notes, fn ($item) => (int) $item['student_id'] === (int) $student['id'])) ?></td>
                                    <td><?= count(array_filter($violations, fn ($item) => (int) $item['student_id'] === (int) $student['id'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($role === 'admin' && $module === 'users'): ?>
            <section class="dashboard-columns">
                <article class="panel">
                    <div class="panel-header"><h2>Tambah User</h2><span>Akun</span></div>
                    <form class="compact-form" action="/action.php" method="post" data-validate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_user">
                        <label>Nama <input type="text" name="name" required minlength="3"></label>
                        <label>Email <input type="email" name="email" required></label>
                        <label>Password <input type="password" name="password" required minlength="6"></label>
                        <label>Role <select name="role"><option value="siswa">Siswa</option><option value="orang_tua">Orang Tua</option><option value="guru_bk">Guru BK</option><option value="admin">Admin</option></select></label>
                        <button class="btn btn-primary" type="submit">Tambah User</button>
                    </form>
                </article>
                <article class="panel">
                    <div class="panel-header"><h2>Daftar User</h2><span><?= count($users) ?> akun</span></div>
                    <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th></tr></thead><tbody><?php foreach ($users as $item): ?><tr><td><?= e($item['name']) ?></td><td><?= e($item['email']) ?></td><td><?= e($item['role']) ?></td><td><?= status_badge($item['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
                </article>
            </section>

        <?php elseif ($role === 'admin' && $module === 'siswa'): ?>
            <section class="dashboard-columns">
                <article class="panel">
                    <div class="panel-header"><h2>Tambah Siswa</h2><span>Data induk</span></div>
                    <form class="compact-form" action="/action.php" method="post" data-validate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_student">
                        <label>Akun Siswa <select name="user_id"><option value="">Belum ditautkan</option><?php foreach ($users as $item): ?><?php if ($item['role'] === 'siswa'): ?><option value="<?= (int) $item['id'] ?>"><?= e($item['name']) ?></option><?php endif; ?><?php endforeach; ?></select></label>
                        <label>NIS <input type="text" name="nis" required minlength="3"></label>
                        <label>Nama <input type="text" name="name" required minlength="3"></label>
                        <label>Kelas <input type="text" name="class_name" required minlength="2"></label>
                        <label>Orang Tua/Wali <input type="text" name="guardian_name"></label>
                        <button class="btn btn-primary" type="submit">Tambah Siswa</button>
                    </form>
                </article>
                <article class="panel">
                    <div class="panel-header"><h2>Data Siswa</h2><span><?= count($students) ?> siswa</span></div>
                    <div class="table-wrap"><table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Wali</th><th>Status</th></tr></thead><tbody><?php foreach ($students as $item): ?><tr><td><?= e($item['nis']) ?></td><td><?= e($item['name']) ?></td><td><?= e($item['class_name']) ?></td><td><?= e($item['guardian_name']) ?></td><td><?= status_badge($item['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
                </article>
            </section>

        <?php elseif ($role === 'admin' && $module === 'statistik'): ?>
            <section class="metric-grid">
                <article class="metric-card"><strong><?= count($users) ?></strong><span>Total User</span></article>
                <article class="metric-card"><strong><?= count($students) ?></strong><span>Total Siswa</span></article>
                <article class="metric-card"><strong><?= count($consultations) ?></strong><span>Konsultasi</span></article>
                <article class="metric-card"><strong><?= count($messages) ?></strong><span>Pesan</span></article>
            </section>

        <?php elseif ($role === 'admin' && $module === 'backup'): ?>
            <section class="panel">
                <div class="panel-header"><h2>Backup Data</h2><span>JSON</span></div>
                <form action="/action.php" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="backup">
                    <button class="btn btn-primary" type="submit">Buat Backup Sekarang</button>
                </form>
                <div class="file-list">
                    <?php foreach (glob(__DIR__ . '/../database/backup-*.json') ?: [] as $file): ?>
                        <span><?= e(basename($file)) ?></span>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script src="<?= asset_path('js/app.js') ?>" defer></script>
</body>
</html>
