<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/provider.php';

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    // Regenerate every 30 minutes
    if (time() - $_SESSION['csrf_token_time'] > 1800) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool {
    if (!$token || empty($_SESSION['csrf_token'])) return false;
    if (!hash_equals($_SESSION['csrf_token'], $token)) return false;
    // Expire after 30 minutes
    if (time() - ($_SESSION['csrf_token_time'] ?? 0) > 1800) return false;
    return true;
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

function csrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

function validateImage(array $file): array {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload gagal. Coba lagi.';
        return $errors;
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $errors[] = 'Ukuran gambar maksimal 5MB.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, ALLOWED_MIMES, true)) {
        $errors[] = 'Format tidak didukung. Gunakan JPG, PNG, WebP, atau GIF.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        $errors[] = 'Ekstensi file tidak valid.';
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'File bukan gambar yang valid.';
    }

    return $errors;
}

function generateRandomId(int $length = 10): string {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $id = '';
    for ($i = 0; $i < $length; $i++) {
        $id .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $id;
}

function saveImageMeta(array $data): ?int {
    $db = getDB();
    if (!$db) return null;
    $stmt = $db->prepare(
        'INSERT INTO images (user_id, provider, provider_image_id, original_name, image_url, thumbnail_url, mime_type, file_size, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $data['user_id'] ?? null,
        $data['provider'],
        $data['provider_image_id'],
        $data['original_name'],
        $data['image_url'],
        $data['thumbnail_url'] ?? null,
        $data['mime_type'],
        $data['file_size'],
    ]);
    return (int) $db->lastInsertId();
}

function getImageById(int $id): ?array {
    $db = getDB();
    if (!$db) return null;
    $stmt = $db->prepare('SELECT * FROM images WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function deleteImageMeta(int $id): bool {
    $db = getDB();
    if (!$db) return false;
    $stmt = $db->prepare('DELETE FROM images WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function checkRateLimit(string $key, int $maxAttempts, int $windowSeconds): bool {
    $db = getDB();
    if (!$db) return true; // no DB = no rate limiting

    try {
        $stmt = $db->prepare(
            'SELECT COUNT(*) as cnt FROM rate_limits WHERE rate_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)'
        );
        $stmt->execute([$key, $windowSeconds]);
        $row = $stmt->fetch();

        if ($row['cnt'] >= $maxAttempts) {
            return false;
        }

        $stmt = $db->prepare('INSERT INTO rate_limits (rate_key, created_at) VALUES (?, NOW())');
        $stmt->execute([$key]);
    } catch (Exception $e) {
        // Table might not exist
    }
    return true;
}

function timeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'baru saja';
}
