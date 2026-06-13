<?php

require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/DataStore.php';

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard.php');
    exit;
}

verify_csrf();

$user = Auth::user();
$role = $user['role'];
$action = $_POST['action'] ?? '';

function redirect_module(string $module): void
{
    header('Location: /dashboard.php?module=' . urlencode($module));
    exit;
}

function require_role(array $roles): void
{
    global $role;
    if (!in_array($role, $roles, true)) {
        flash('error', 'Akses tidak diizinkan untuk role ini.');
        redirect_module('overview');
    }
}

function clean_text(string $key, int $min = 1): string
{
    $value = trim($_POST[$key] ?? '');
    if (strlen($value) < $min) {
        flash('error', 'Data belum lengkap. Mohon isi form dengan benar.');
        redirect_module($_POST['module'] ?? 'overview');
    }

    return $value;
}

switch ($action) {
    case 'request_consultation':
        require_role(['siswa']);
        $student = DataStore::findStudentByUser((int) $user['id']);
        if (!$student) {
            flash('error', 'Data siswa belum terhubung dengan akun ini.');
            redirect_module('konsultasi');
        }

        DataStore::add('consultations', [
            'student_id' => $student['id'],
            'user_id' => $user['id'],
            'topic' => clean_text('topic', 4),
            'message' => clean_text('message', 10),
            'preferred_date' => $_POST['preferred_date'] ?? null,
            'status' => 'pending',
            'response' => '',
        ]);
        DataStore::addNotification(3, 'Pengajuan konsultasi baru', $user['name'] . ' mengajukan konsultasi BK.');
        flash('success', 'Pengajuan konsultasi berhasil dikirim.');
        redirect_module('riwayat');

    case 'send_message':
        require_role(['siswa', 'orang_tua', 'guru_bk']);
        $toUserId = (int) ($_POST['to_user_id'] ?? 3);
        DataStore::add('messages', [
            'from_user_id' => (int) $user['id'],
            'to_user_id' => $toUserId,
            'body' => clean_text('body', 3),
        ]);
        DataStore::addNotification($toUserId, 'Pesan baru', 'Anda menerima pesan dari ' . $user['name'] . '.');
        flash('success', 'Pesan berhasil dikirim.');
        redirect_module($_POST['module'] ?? 'chat');

    case 'update_consultation':
        require_role(['guru_bk']);
        $consultationId = (int) ($_POST['consultation_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $allowed = ['pending', 'approved', 'completed', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $data = DataStore::all();
        $consultation = null;
        foreach ($data['consultations'] as $item) {
            if ((int) $item['id'] === $consultationId) {
                $consultation = $item;
                break;
            }
        }

        if (!$consultation) {
            flash('error', 'Data konsultasi tidak ditemukan.');
            redirect_module('konsultasi');
        }

        DataStore::update('consultations', $consultationId, [
            'status' => $status,
            'response' => trim($_POST['response'] ?? ''),
        ]);

        if ($status === 'approved' && !empty($_POST['schedule_at'])) {
            DataStore::add('schedules', [
                'consultation_id' => $consultationId,
                'student_id' => (int) $consultation['student_id'],
                'counselor_id' => (int) $user['id'],
                'schedule_at' => str_replace('T', ' ', $_POST['schedule_at']) . ':00',
                'status' => 'scheduled',
                'notes' => trim($_POST['response'] ?? ''),
            ]);
        }

        DataStore::addNotification((int) $consultation['user_id'], 'Status konsultasi diperbarui', 'Pengajuan "' . $consultation['topic'] . '" sekarang berstatus ' . $status . '.');
        flash('success', 'Status konsultasi berhasil diperbarui.');
        redirect_module('konsultasi');

    case 'add_note':
        require_role(['guru_bk']);
        DataStore::add('notes', [
            'student_id' => (int) ($_POST['student_id'] ?? 0),
            'counselor_id' => (int) $user['id'],
            'title' => clean_text('title', 4),
            'body' => clean_text('body', 10),
        ]);
        flash('success', 'Catatan perkembangan berhasil disimpan.');
        redirect_module('perkembangan');

    case 'add_violation':
        require_role(['guru_bk']);
        DataStore::add('violations', [
            'student_id' => (int) ($_POST['student_id'] ?? 0),
            'recorded_by' => (int) $user['id'],
            'category' => clean_text('category', 3),
            'description' => clean_text('description', 6),
            'point' => max(0, (int) ($_POST['point'] ?? 0)),
            'incident_date' => $_POST['incident_date'] ?: date('Y-m-d'),
        ]);
        flash('success', 'Data pelanggaran berhasil dicatat.');
        redirect_module('pelanggaran');

    case 'add_user':
        require_role(['admin']);
        $email = strtolower(clean_text('email', 6));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Email user tidak valid.');
            redirect_module('users');
        }
        if (DataStore::findUserByEmail($email)) {
            flash('error', 'Email sudah digunakan.');
            redirect_module('users');
        }
        DataStore::add('users', [
            'name' => clean_text('name', 3),
            'email' => $email,
            'password' => password_hash(clean_text('password', 6), PASSWORD_DEFAULT),
            'role' => $_POST['role'] ?? 'siswa',
            'status' => 'active',
        ]);
        flash('success', 'User baru berhasil dibuat.');
        redirect_module('users');

    case 'add_student':
        require_role(['admin']);
        DataStore::add('students', [
            'user_id' => (int) ($_POST['user_id'] ?? 0) ?: null,
            'nis' => clean_text('nis', 3),
            'name' => clean_text('name', 3),
            'class_name' => clean_text('class_name', 2),
            'guardian_name' => trim($_POST['guardian_name'] ?? ''),
            'status' => 'Aktif',
        ]);
        flash('success', 'Data siswa berhasil ditambahkan.');
        redirect_module('siswa');

    case 'backup':
        require_role(['admin']);
        $file = DataStore::backup();
        flash('success', 'Backup berhasil dibuat: ' . $file);
        redirect_module('backup');

    default:
        flash('error', 'Aksi tidak dikenali.');
        redirect_module('overview');
}
