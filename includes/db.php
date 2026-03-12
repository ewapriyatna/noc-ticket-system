<?php
/**
 * Database connection helper using PDO.
 */

require_once __DIR__ . '/../config.php';

/**
 * Return a PDO database connection (singleton).
 *
 * @return PDO
 * @throws RuntimeException if the connection cannot be established.
 */
function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $message = defined('APP_DEBUG') && APP_DEBUG
                ? 'Database connection failed: ' . $e->getMessage()
                : 'Database connection failed.';
            throw new RuntimeException($message, 0, $e);
        }
    }

    return $pdo;
}
