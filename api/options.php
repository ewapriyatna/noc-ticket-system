<?php
/**
 * API endpoint: System options (regional list, vendor list, status list).
 *
 * GET /api/options.php
 *   Returns { success: true, data: { regional: [], vendor: [], status: [] } }
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

set_cors_headers();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(false, null, 'Method not allowed.', 405);
}

try {
    $pdo = get_db();

    $regional = $pdo->query('SELECT name FROM regional_options ORDER BY name')
                    ->fetchAll(PDO::FETCH_COLUMN);

    $vendor = $pdo->query('SELECT name FROM vendor_options ORDER BY name')
                  ->fetchAll(PDO::FETCH_COLUMN);

    $status = ['Open', 'Progress', 'Escalated', 'Closed'];

    json_response(true, [
        'regional' => $regional,
        'vendor'   => $vendor,
        'status'   => $status,
    ]);
} catch (Exception $e) {
    $msg = defined('APP_DEBUG') && APP_DEBUG ? $e->getMessage() : 'Internal server error.';
    json_response(false, null, $msg, 500);
}
