<?php
/**
 * connexion_bd.php
 * Fichier de connexion PDO à la base de données MySQL - FoyerConnect
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'foyerconnect');
define('DB_USER', 'root');       // Modifier selon votre configuration
define('DB_PASS', '');           // Modifier selon votre configuration
define('DB_CHARSET', 'utf8mb4');

/**
 * Retourne une instance PDO (singleton)
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En production : logger l'erreur, ne pas l'afficher
            die(json_encode(['error' => 'Connexion impossible : ' . $e->getMessage()]));
        }
    }
    return $pdo;
}