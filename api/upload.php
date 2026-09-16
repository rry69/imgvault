<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../providers/ImgBB.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

    // CSRF check
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!validateCsrfToken($csrfToken)) {
        jsonResponse(['success' => false, 'error' => 'Token keamanan tidak valid.'], 403);
    }

    // Support both single and bulk upload
    if (!isset($_FILES['images']) && !isset($_FILES['image'])) {
        jsonResponse(['success' => false, 'error' => 'Tidak ada gambar yang dikirim.'], 400);
    }

    $files = $_FILES['images'] ?? $_FILES['image'];

    // Normalize to array of files
    if (!is_array($files['name'])) {
        $files = [
            'name'     => [$files['name']],
            'type'     => [$files['type']],
            'tmp_name' => [$files['tmp_name']],
            'error'    => [$files['error']],
            'size'     => [$files['size']],
        ];
    }

    $count = count($files['name']);

    // Rate limit
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $guestKey = 'guest:' . $ip;
    if (!checkRateLimit($guestKey, 60, 3600)) {
        jsonResponse(['success' => false, 'error' => 'Batas upload tercapai (60/jam).'], 429);
    }

    $imgbb = new ImgBB(IMGBB_API_KEY, IMGBB_UPLOAD_URL);
    $results = [];

    for ($i = 0; $i < $count; $i++) {
        $file = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];

        $errors = validateImage($file);
        if (!empty($errors)) {
            $results[] = [
                'success' => false,
                'filename' => $file['name'],
                'error' => implode(' ', $errors),
            ];
            continue;
        }

        $result = $imgbb->upload($file['tmp_name']);

        if ($result === false) {
            $results[] = [
                'success' => false,
                'filename' => $file['name'],
                'error' => 'Gagal upload ke ImgBB.',
            ];
            continue;
        }

        // Save metadata (optional — don't crash if DB missing)
        $imageId = null;
        try {
            $imageId = saveImageMeta([
                'user_id' => null,
                'provider' => 'imgbb',
                'provider_image_id' => $result['id'] ?? null,
                'original_name' => $file['name'],
                'image_url' => $result['url'],
                'thumbnail_url' => $result['thumb']['url'] ?? null,
                'mime_type' => $result['image']['mime'] ?? $file['type'],
                'file_size' => (int) $result['size'],
            ]);
        } catch (Exception $e) {
            // DB not available — still return the URL
        }

        $results[] = [
            'success' => true,
            'id' => $imageId,
            'filename' => $file['name'],
            'url' => $result['url'],
            'display_url' => $result['display_url'] ?? $result['url'],
            'delete_url' => $result['delete_url'] ?? null,
            'width' => $result['width'] ?? null,
            'height' => $result['height'] ?? null,
            'size' => $result['size'] ?? null,
            'mime' => $result['image']['mime'] ?? null,
        ];
    }

    // Single file: return flat. Multi: return array.
    if ($count === 1) {
        jsonResponse($results[0]);
    }

    jsonResponse(['success' => true, 'count' => count(array_filter($results, fn($r) => $r['success'])), 'results' => $results]);

} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] upload.php error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    jsonResponse(['success' => false, 'error' => 'Terjadi kesalahan server. Silakan coba lagi.'], 500);
}
