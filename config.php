<?php
// Database connection settings
$DB_HOST = 'localhost';
$DB_NAME = 'carworkshop_db';
$DB_USER = 'root';
$DB_PASS = ''; // leave empty unless you set a password in MySQL

/**
 * Create and return a PDO database connection
 */
function db() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    static $pdo = null; // reuse same connection
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
                $DB_USER,
                $DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Return JSON response and exit
 */
function json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Trim and sanitize input string
 */
function sanitize($s) {
    return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8');
}
