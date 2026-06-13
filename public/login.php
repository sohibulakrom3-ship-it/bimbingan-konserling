<?php
require_once __DIR__ . '/../app/Auth.php';

if (Auth::check()) {
    header('Location: /dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if (!$errors && Auth::attempt($email, $password)) {
        header('Location: /dashboard.php');
        exit;
    }

    if (!$errors) {
        $errors[] = 'Email atau password tidak sesuai.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login Sistem Informasi BK SMP Muhammadiyah Cileungsi.">
    <title>Login - BK SMP Muhammadiyah Cileungsi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_path('css/styles.css') ?>">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-panel">
            <a class="brand" href="/">
                <span class="brand-mark">BK</span>
                <span><strong>SMP Muhammadiyah</strong><small>Cileungsi</small></span>
            </a>
            <h1>Masuk ke Sistem BK</h1>
            <p>Gunakan akun sesuai role untuk mengakses layanan konseling digital sekolah.</p>

            <?php if ($errors): ?>
                <div class="alert-box" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <span><?= e($error) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="form-card" method="post" data-validate>
                <?= csrf_field() ?>
                <label>
                    Email
                    <input type="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" placeholder="Minimal 6 karakter" minlength="6" required>
                </label>
                <button class="btn btn-primary" type="submit">Login</button>
            </form>

            <div class="demo-box">
                <strong>Akun demo</strong>
                <span>siswa@bk.test / password</span>
                <span>ortu@bk.test / password</span>
                <span>guru@bk.test / password</span>
                <span>admin@bk.test / password</span>
            </div>
        </section>
        <section class="auth-visual" aria-label="Ringkasan manfaat sistem">
            <img src="<?= asset_path('images/hero-konseling.png') ?>" alt="Ilustrasi layanan konseling digital">
            <div class="auth-stats">
                <strong>96%</strong>
                <span>Kepuasan layanan BK digital</span>
            </div>
        </section>
    </main>
    <script src="<?= asset_path('js/app.js') ?>" defer></script>
</body>
</html>
