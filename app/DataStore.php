<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Database.php';

final class DataStore
{
    private const FILE = __DIR__ . '/../database/demo-data.json';

    public static function all(): array
    {
        $pdo = self::pdo();

        if ($pdo instanceof PDO) {
            self::ensureMysqlSupport($pdo);
            return self::fromMysql($pdo);
        }

        self::ensureFile();
        $content = file_get_contents(self::FILE);
        $data = json_decode($content ?: '', true);

        return is_array($data) ? $data : self::seed();
    }

    public static function save(array $data): void
    {
        $data['meta']['updated_at'] = date('Y-m-d H:i:s');
        file_put_contents(self::FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public static function users(): array
    {
        return self::all()['users'];
    }

    public static function findUserByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        foreach (self::users() as $user) {
            if (strtolower($user['email']) === $email && ($user['status'] ?? 'active') === 'active') {
                return $user;
            }
        }

        return null;
    }

    public static function findUser(int $id): ?array
    {
        foreach (self::users() as $user) {
            if ((int) $user['id'] === $id) {
                return $user;
            }
        }

        return null;
    }

    public static function findStudentByUser(int $userId): ?array
    {
        foreach (self::all()['students'] as $student) {
            if ((int) ($student['user_id'] ?? 0) === $userId) {
                return $student;
            }
        }

        return null;
    }

    public static function childrenForParent(int $parentId): array
    {
        $data = self::all();
        $studentIds = [];

        foreach ($data['parent_student'] as $relation) {
            if ((int) $relation['parent_user_id'] === $parentId) {
                $studentIds[] = (int) $relation['student_id'];
            }
        }

        return array_values(array_filter($data['students'], fn ($student) => in_array((int) $student['id'], $studentIds, true)));
    }

    public static function nextId(array $items): int
    {
        $ids = array_map(fn ($item) => (int) ($item['id'] ?? 0), $items);
        return $ids ? max($ids) + 1 : 1;
    }

    public static function add(string $collection, array $item): array
    {
        $pdo = self::pdo();

        if ($pdo instanceof PDO) {
            self::ensureMysqlSupport($pdo);
            return self::insertMysql($pdo, $collection, $item);
        }

        $data = self::all();
        $item['id'] = self::nextId($data[$collection] ?? []);
        $item['created_at'] = date('Y-m-d H:i:s');
        $data[$collection][] = $item;
        self::save($data);

        return $item;
    }

    public static function update(string $collection, int $id, array $values): void
    {
        $pdo = self::pdo();

        if ($pdo instanceof PDO) {
            self::ensureMysqlSupport($pdo);
            self::updateMysql($pdo, $collection, $id, $values);
            return;
        }

        $data = self::all();
        foreach ($data[$collection] as &$item) {
            if ((int) $item['id'] === $id) {
                $item = array_merge($item, $values, ['updated_at' => date('Y-m-d H:i:s')]);
                break;
            }
        }
        unset($item);
        self::save($data);
    }

    public static function addNotification(int $userId, string $title, string $body): void
    {
        self::add('notifications', [
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
        ]);
    }

    public static function backup(): string
    {
        $data = self::all();
        $target = __DIR__ . '/../database/backup-db-' . date('Ymd-His') . '.json';
        file_put_contents($target, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return basename($target);
    }

    private static function pdo(): ?PDO
    {
        return Database::connection();
    }

    private static function fromMysql(PDO $pdo): array
    {
        return [
            'meta' => ['updated_at' => date('Y-m-d H:i:s'), 'source' => 'mysql'],
            'users' => self::fetchAll($pdo, 'SELECT id, name, email, password, role, status, created_at, updated_at FROM users ORDER BY id'),
            'students' => self::fetchAll($pdo, 'SELECT id, user_id, nis, name, class_name, guardian_name, "Aktif" AS status, created_at, updated_at FROM students ORDER BY id'),
            'parent_student' => self::fetchAll($pdo, 'SELECT id, parent_user_id, student_id, relation, created_at FROM parent_student ORDER BY id'),
            'consultations' => self::fetchAll($pdo, 'SELECT cr.id, COALESCE(s.id, 0) AS student_id, cr.user_id, cr.topic, cr.message, cr.preferred_date, cr.status, COALESCE(cr.response, "") AS response, cr.created_at, cr.updated_at FROM counseling_requests cr LEFT JOIN students s ON s.user_id = cr.user_id ORDER BY cr.id DESC'),
            'schedules' => self::fetchAll($pdo, 'SELECT id, request_id AS consultation_id, student_id, counselor_id, schedule_at, status, notes, created_at, updated_at FROM counseling_sessions ORDER BY id DESC'),
            'notes' => self::fetchAll($pdo, 'SELECT id, student_id, counselor_id, title, body, created_at, updated_at FROM counseling_notes ORDER BY id DESC'),
            'violations' => self::fetchAll($pdo, 'SELECT id, student_id, recorded_by, category, description, point, incident_date, created_at FROM violations ORDER BY id DESC'),
            'notifications' => self::fetchAll($pdo, 'SELECT id, user_id, title, body, read_at, created_at FROM notifications ORDER BY id DESC'),
            'messages' => self::fetchAll($pdo, 'SELECT id, from_user_id, to_user_id, body, created_at FROM messages ORDER BY id ASC'),
        ];
    }

    private static function fetchAll(PDO $pdo, string $sql): array
    {
        return $pdo->query($sql)->fetchAll();
    }

    private static function insertMysql(PDO $pdo, string $collection, array $item): array
    {
        switch ($collection) {
            case 'users':
                $statement = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, :role, :status)');
                $statement->execute([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'password' => $item['password'],
                    'role' => $item['role'],
                    'status' => $item['status'] ?? 'active',
                ]);
                break;

            case 'students':
                $statement = $pdo->prepare('INSERT INTO students (user_id, nis, name, class_name, guardian_name) VALUES (:user_id, :nis, :name, :class_name, :guardian_name)');
                $statement->execute([
                    'user_id' => $item['user_id'] ?: null,
                    'nis' => $item['nis'],
                    'name' => $item['name'],
                    'class_name' => $item['class_name'],
                    'guardian_name' => $item['guardian_name'] ?? null,
                ]);
                break;

            case 'consultations':
                $statement = $pdo->prepare('INSERT INTO counseling_requests (user_id, topic, message, preferred_date, status, response) VALUES (:user_id, :topic, :message, :preferred_date, :status, :response)');
                $statement->execute([
                    'user_id' => $item['user_id'],
                    'topic' => $item['topic'],
                    'message' => $item['message'],
                    'preferred_date' => self::datetimeOrNull($item['preferred_date'] ?? null),
                    'status' => $item['status'] ?? 'pending',
                    'response' => $item['response'] ?? '',
                ]);
                break;

            case 'schedules':
                $statement = $pdo->prepare('INSERT INTO counseling_sessions (request_id, student_id, counselor_id, schedule_at, status, notes) VALUES (:request_id, :student_id, :counselor_id, :schedule_at, :status, :notes)');
                $statement->execute([
                    'request_id' => $item['consultation_id'] ?? null,
                    'student_id' => $item['student_id'],
                    'counselor_id' => $item['counselor_id'],
                    'schedule_at' => self::datetimeOrNull($item['schedule_at']) ?: date('Y-m-d H:i:s'),
                    'status' => $item['status'] ?? 'scheduled',
                    'notes' => $item['notes'] ?? null,
                ]);
                break;

            case 'notes':
                $statement = $pdo->prepare('INSERT INTO counseling_notes (student_id, counselor_id, title, body) VALUES (:student_id, :counselor_id, :title, :body)');
                $statement->execute([
                    'student_id' => $item['student_id'],
                    'counselor_id' => $item['counselor_id'],
                    'title' => $item['title'],
                    'body' => $item['body'],
                ]);
                break;

            case 'violations':
                $statement = $pdo->prepare('INSERT INTO violations (student_id, recorded_by, category, description, point, incident_date) VALUES (:student_id, :recorded_by, :category, :description, :point, :incident_date)');
                $statement->execute([
                    'student_id' => $item['student_id'],
                    'recorded_by' => $item['recorded_by'],
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'point' => $item['point'] ?? 0,
                    'incident_date' => $item['incident_date'] ?? date('Y-m-d'),
                ]);
                break;

            case 'notifications':
                $statement = $pdo->prepare('INSERT INTO notifications (user_id, title, body, read_at) VALUES (:user_id, :title, :body, :read_at)');
                $statement->execute([
                    'user_id' => $item['user_id'],
                    'title' => $item['title'],
                    'body' => $item['body'],
                    'read_at' => $item['read_at'] ?? null,
                ]);
                break;

            case 'messages':
                $statement = $pdo->prepare('INSERT INTO messages (from_user_id, to_user_id, body) VALUES (:from_user_id, :to_user_id, :body)');
                $statement->execute([
                    'from_user_id' => $item['from_user_id'],
                    'to_user_id' => $item['to_user_id'],
                    'body' => $item['body'],
                ]);
                break;

            case 'parent_student':
                $statement = $pdo->prepare('INSERT INTO parent_student (parent_user_id, student_id, relation) VALUES (:parent_user_id, :student_id, :relation)');
                $statement->execute([
                    'parent_user_id' => $item['parent_user_id'],
                    'student_id' => $item['student_id'],
                    'relation' => $item['relation'] ?? 'orang_tua',
                ]);
                break;

            default:
                throw new InvalidArgumentException('Collection tidak dikenali: ' . $collection);
        }

        $item['id'] = (int) $pdo->lastInsertId();
        $item['created_at'] = date('Y-m-d H:i:s');

        return $item;
    }

    private static function updateMysql(PDO $pdo, string $collection, int $id, array $values): void
    {
        if ($collection === 'consultations') {
            $statement = $pdo->prepare('UPDATE counseling_requests SET status = :status, response = :response WHERE id = :id');
            $statement->execute([
                'id' => $id,
                'status' => $values['status'] ?? 'pending',
                'response' => $values['response'] ?? '',
            ]);
            return;
        }

        throw new InvalidArgumentException('Update MySQL belum tersedia untuk collection: ' . $collection);
    }

    private static function ensureMysqlSupport(PDO $pdo): void
    {
        self::ensureColumn($pdo, 'counseling_requests', 'response', 'TEXT NULL');

        $pdo->exec('CREATE TABLE IF NOT EXISTS counseling_notes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id BIGINT UNSIGNED NOT NULL,
            counselor_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(160) NOT NULL,
            body TEXT NOT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT counseling_notes_student_fk FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            CONSTRAINT counseling_notes_counselor_fk FOREIGN KEY (counselor_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            from_user_id BIGINT UNSIGNED NOT NULL,
            to_user_id BIGINT UNSIGNED NOT NULL,
            body TEXT NOT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT messages_from_user_fk FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT messages_to_user_fk FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private static function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
    {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $statement->execute([
            'table' => $table,
            'column' => $column,
        ]);

        if ((int) $statement->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
        }
    }

    private static function datetimeOrNull(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value . ' 00:00:00';
        }

        if (str_contains($value, 'T')) {
            return str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
        }

        return $value;
    }

    private static function ensureFile(): void
    {
        if (!is_file(self::FILE)) {
            self::save(self::seed());
        }
    }

    private static function seed(): array
    {
        $password = password_hash('password', PASSWORD_DEFAULT);

        return [
            'meta' => ['updated_at' => date('Y-m-d H:i:s'), 'source' => 'json'],
            'users' => [
                ['id' => 1, 'name' => 'Aisyah Ramadhani', 'email' => 'siswa@bk.test', 'password' => $password, 'role' => 'siswa', 'status' => 'active'],
                ['id' => 2, 'name' => 'Ibu Nurul', 'email' => 'ortu@bk.test', 'password' => $password, 'role' => 'orang_tua', 'status' => 'active'],
                ['id' => 3, 'name' => 'Pak Ahmad Fauzi', 'email' => 'guru@bk.test', 'password' => $password, 'role' => 'guru_bk', 'status' => 'active'],
                ['id' => 4, 'name' => 'Admin Sekolah', 'email' => 'admin@bk.test', 'password' => $password, 'role' => 'admin', 'status' => 'active'],
            ],
            'students' => [
                ['id' => 1, 'user_id' => 1, 'nis' => '2425001', 'name' => 'Aisyah Ramadhani', 'class_name' => 'VIII A', 'guardian_name' => 'Ibu Nurul', 'status' => 'Aktif'],
            ],
            'parent_student' => [
                ['id' => 1, 'parent_user_id' => 2, 'student_id' => 1, 'relation' => 'Ibu'],
            ],
            'consultations' => [],
            'schedules' => [],
            'notes' => [],
            'violations' => [],
            'notifications' => [],
            'messages' => [],
        ];
    }
}
