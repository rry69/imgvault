<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDB();
    if (!$db) {
        jsonResponse(['success' => false, 'error' => 'Database not available'], 500);
    }

    // Get total images count and total size
    $stmt = $db->query('SELECT COUNT(*) as total_images, COALESCE(SUM(file_size), 0) as total_size FROM images');
    $stats = $stmt->fetch();

    // Get latest image
    $stmt = $db->query('SELECT * FROM images ORDER BY created_at DESC LIMIT 1');
    $latest = $stmt->fetch();

    jsonResponse([
        'success' => true,
        'total_images' => (int) $stats['total_images'],
        'total_size' => (int) $stats['total_size'],
        'latest_image' => $latest ? [
            'id' => $latest['id'],
            'filename' => $latest['original_name'],
            'url' => $latest['image_url'],
            'thumbnail_url' => $latest['thumbnail_url'],
            'file_size' => (int) $latest['file_size'],
            'mime_type' => $latest['mime_type'],
            'created_at' => $latest['created_at'],
        ] : null,
    ]);

} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] stats.php error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    jsonResponse(['success' => false, 'error' => 'Terjadi kesalahan server.'], 500);
}
