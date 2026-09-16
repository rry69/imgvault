<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'image_hosting');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB(): ?PDO {
    static $pdo = null;
    static $failed = false;
    if ($failed) return null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Exception $e) {
            $failed = true;
            return null;
        }
    }
    return $pdo;
}
