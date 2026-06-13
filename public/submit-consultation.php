<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/DataStore.php';

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard.php');
    exit;
}

verify_csrf();

$topic = trim($_POST['topic'] ?? '');
$message = trim($_POST['message'] ?? '');

if (strlen($topic) < 4 || strlen($message) < 10) {
    flash('success', 'Data belum lengkap. Mohon isi topik dan pesan dengan benar.');
    header('Location: /dashboard.php#laporan');
    exit;
}

$pdo = Database::connection();
$user = Auth::user();
$student = DataStore::findStudentByUser((int) $user['id']);

if ($pdo instanceof PDO) {
    $statement = $pdo->prepare('INSERT INTO counseling_requests (user_id, topic, message, status, created_at) VALUES (:user_id, :topic, :message, "pending", NOW())');
    $statement->execute([
        'user_id' => $user['id'],
        'topic' => $topic,
        'message' => $message,
    ]);
}

if ($student) {
    DataStore::add('consultations', [
        'student_id' => $student['id'],
        'user_id' => $user['id'],
        'topic' => $topic,
        'message' => $message,
        'preferred_date' => null,
        'status' => 'pending',
        'response' => '',
    ]);
}

flash('success', 'Pengajuan berhasil disimpan. Guru BK akan meninjau sesuai jadwal layanan.');
header('Location: /dashboard.php#laporan');
exit;
