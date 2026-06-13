<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/DataStore.php';

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $user = self::findUser($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        unset($user['password']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;

        return true;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    private static function findUser(string $email): ?array
    {
        $pdo = Database::connection();

        if ($pdo instanceof PDO) {
            $statement = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email AND status = "active" LIMIT 1');
            $statement->execute(['email' => $email]);
            $user = $statement->fetch();

            if ($user) {
                return $user;
            }
        }

        return DataStore::findUserByEmail($email);
    }
}
