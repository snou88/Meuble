<?php
/**
 * Configuration de la base de données
 */
/* EN LIGNE HOST  */
/* define('DB_HOST', 'sql110.infinityfree.com');
define('DB_NAME', 'if0_40763827_ama');
define('DB_USER', 'if0_40763827');
define('DB_PASS', 'bY2T3V0ve6');
define('DB_CHARSET', 'utf8mb4'); */
/* LOCALE HOST  */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ama');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Connexion à la base de données
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erreur de connexion à la base de données']);
        exit;
    }
}

/**
 * Fonction utilitaire pour les réponses JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Fonction pour nettoyer les entrées
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

