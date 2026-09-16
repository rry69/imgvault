<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../providers/ImgBB.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// CSRF check
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? null;
if (!validateCsrfToken($csrfToken)) {
    jsonResponse(['success' => false, 'error' => 'Token keamanan tidak valid.'], 403);
}

// Rate limit: 30 deletes per hour
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$deleteKey = 'delete:' . $ip;
if (!checkRateLimit($deleteKey, 30, 3600)) {
    jsonResponse(['success' => false, 'error' => 'Batas hapus tercapai (30/jam).'], 429);
}

$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
    jsonResponse(['success' => false, 'error' => 'ID tidak valid.'], 400);
}

$image = getImageById((int) $id);
if (!$image) {
    jsonResponse(['success' => false, 'error' => 'Gambar tidak ditemukan.'], 404);
}

$imgbb = new ImgBB(IMGBB_API_KEY, IMGBB_UPLOAD_URL);

// Use correct ImgBB delete URL format
if ($image['provider_image_id'] && IMGBB_API_KEY) {
    $deleteUrl = 'https://api.imgbb.com/1/delete?key=' . IMGBB_API_KEY . '&image_id=' . $image['provider_image_id'];
    $imgbb->delete($deleteUrl);
}

deleteImageMeta((int) $id);

jsonResponse(['success' => true]);
