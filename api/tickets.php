<?php
/**
 * API endpoint: Ticket CRUD operations.
 *
 * GET    /api/tickets.php            – list all tickets (supports ?status=, ?vendor=, ?regional=, ?search=)
 * GET    /api/tickets.php?id={id}    – get single ticket
 * POST   /api/tickets.php            – create ticket
 * PUT    /api/tickets.php?id={id}    – update ticket
 * DELETE /api/tickets.php?id={id}    – delete ticket
 *
 * All responses: { success: bool, data?: mixed, message?: string }
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

set_cors_headers();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

// Read JSON body for PUT/POST when Content-Type is application/json
$body = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $raw = file_get_contents('php://input');
    if ($raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $body = $decoded;
        }
    }
} else {
    // form-encoded body (POST/PUT via URLSearchParams)
    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $body);
    } else {
        $body = $_POST;
    }
}

// Merge query params (GET) so helper functions work uniformly
$params = array_merge($_GET, $body);

try {
    $pdo = get_db();

    switch ($method) {
        case 'GET':
            handle_get($pdo, $params);
            break;

        case 'POST':
            handle_create($pdo, $params);
            break;

        case 'PUT':
            handle_update($pdo, $params);
            break;

        case 'DELETE':
            handle_delete($pdo, $params);
            break;

        default:
            json_response(false, null, 'Method not allowed.', 405);
    }
} catch (Exception $e) {
    $msg = defined('APP_DEBUG') && APP_DEBUG ? $e->getMessage() : 'Internal server error.';
    json_response(false, null, $msg, 500);
}

// ============================================================
// Handlers
// ============================================================

/**
 * GET – list tickets or fetch a single ticket by id.
 */
function handle_get(PDO $pdo, array $params): void
{
    // Single ticket
    if (!empty($params['id'])) {
        $id = (int) $params['id'];
        $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            json_response(false, null, 'Ticket not found.', 404);
        }
        json_response(true, format_ticket($ticket));
    }

    // List with optional filters
    $where  = [];
    $values = [];

    if (!empty($params['status'])) {
        $where[]  = 'status = ?';
        $values[] = $params['status'];
    }
    if (!empty($params['vendor'])) {
        $where[]  = 'vendor = ?';
        $values[] = $params['vendor'];
    }
    if (!empty($params['regional'])) {
        $where[]  = 'regional = ?';
        $values[] = $params['regional'];
    }
    if (!empty($params['search'])) {
        $like     = '%' . $params['search'] . '%';
        $where[]  = '(tt_customer LIKE ? OR cid LIKE ? OR tt_description LIKE ?)';
        $values[] = $like;
        $values[] = $like;
        $values[] = $like;
    }

    $sql = 'SELECT * FROM tickets';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    $tickets = $stmt->fetchAll();

    json_response(true, array_map('format_ticket', $tickets));
}

/**
 * POST – create a new ticket.
 */
function handle_create(PDO $pdo, array $params): void
{
    $required = [
        'tt_customer', 'tt_tbg', 'tt_description',
        'device_segment', 'regional', 'vendor',
        'segment_problem', 'cid', 'start_time',
    ];

    $data = array_map('trim', [
        'tt_customer'     => $params['tt_customer']     ?? ($params['TT_Customer']     ?? ''),
        'tt_tbg'          => $params['tt_tbg']          ?? ($params['TT_TBG']          ?? ''),
        'tt_description'  => $params['tt_description']  ?? ($params['TT_Description']  ?? ''),
        'device_segment'  => $params['device_segment']  ?? ($params['Device_Segment']  ?? ''),
        'regional'        => $params['regional']        ?? ($params['Regional']        ?? ''),
        'vendor'          => $params['vendor']          ?? ($params['Vendor']          ?? ''),
        'segment_problem' => $params['segment_problem'] ?? ($params['Segment_Problem'] ?? ''),
        'cid'             => $params['cid']             ?? ($params['CID']             ?? ''),
        'segment_length'  => $params['segment_length']  ?? ($params['Segment_Length']  ?? ''),
        'start_time'      => $params['start_time']      ?? ($params['Start_Time']      ?? ''),
    ]);

    $error = validate_required($required, $data);
    if ($error !== '') {
        json_response(false, null, $error, 422);
    }

    $startTime = parse_datetime($data['start_time']);
    if ($startTime === null) {
        json_response(false, null, 'Invalid start_time format.', 422);
    }

    $nowStr = date('Y-m-d H:i:s');
    $progressLog = '[' . date('d/m/Y H:i:s') . '] - Ticket Dibuat';

    $stmt = $pdo->prepare(
        'INSERT INTO tickets
            (tt_customer, tt_tbg, tt_description, device_segment, regional, vendor,
             segment_problem, cid, segment_length, start_time, status, progress_log, created_at, updated_at)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'Open\', ?, ?, ?)'
    );

    $stmt->execute([
        $data['tt_customer'],
        $data['tt_tbg'],
        $data['tt_description'],
        $data['device_segment'],
        $data['regional'],
        $data['vendor'],
        $data['segment_problem'],
        $data['cid'],
        $data['segment_length'] ?: null,
        $startTime,
        $progressLog,
        $nowStr,
        $nowStr,
    ]);

    $id = (int) $pdo->lastInsertId();
    $newTicket = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
    $newTicket->execute([$id]);

    json_response(true, format_ticket($newTicket->fetch()), 'Ticket berhasil dibuat.', 201);
}

/**
 * PUT – update an existing ticket.
 */
function handle_update(PDO $pdo, array $params): void
{
    $id = isset($params['id']) ? (int) $params['id'] : 0;

    // Legacy: also accept tt_customer as identifier
    if ($id === 0 && !empty($params['TT_Customer'])) {
        $stmt = $pdo->prepare('SELECT id FROM tickets WHERE tt_customer = ?');
        $stmt->execute([trim($params['TT_Customer'])]);
        $row = $stmt->fetch();
        if ($row) {
            $id = (int) $row['id'];
        }
    }

    if ($id === 0) {
        json_response(false, null, 'Ticket ID is required.', 422);
    }

    $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
    $stmt->execute([$id]);
    $ticket = $stmt->fetch();
    if (!$ticket) {
        json_response(false, null, 'Ticket not found.', 404);
    }

    // Collect fields to update (use existing value as fallback)
    $fields = [
        'status'             => trim($params['status']             ?? ($params['Status']             ?? $ticket['status'])),
        'tt_description'     => trim($params['tt_description']     ?? ($params['TT_Description']     ?? $ticket['tt_description'])),
        'root_cause'         => trim($params['root_cause']         ?? ($params['Root_Cause']         ?? ($ticket['root_cause'] ?? ''))),
        'responsibility'     => trim($params['responsibility']     ?? ($params['Responsibility']     ?? ($ticket['responsibility'] ?? ''))),
        'problem_coordinate' => trim($params['problem_coordinate'] ?? ($params['Problem_Coordinate'] ?? ($ticket['problem_coordinate'] ?? ''))),
        'restoration_action' => trim($params['restoration_action'] ?? ($params['Restoration_Action'] ?? ($ticket['restoration_action'] ?? ''))),
    ];

    // Validate status enum
    $allowedStatuses = ['Open', 'Progress', 'Escalated', 'Closed'];
    if (!in_array($fields['status'], $allowedStatuses, true)) {
        json_response(false, null, 'Invalid status value.', 422);
    }

    // Handle progress log update
    $progressUpdate = trim($params['progress_update'] ?? '');
    $existingLog    = $ticket['progress_log'] ?? '';
    if ($progressUpdate !== '') {
        $fields['progress_log'] = $existingLog . "\n[" . date('d/m/Y H:i:s') . '] ' . $progressUpdate;
    } else {
        $fields['progress_log'] = $existingLog;
    }

    // Handle resolved_time when closing.
    // Append a closure entry only when the ticket was NOT already closed
    // (i.e. the previous status was not 'Closed'), to avoid duplicate entries.
    $resolvedTime = null;
    if ($fields['status'] === 'Closed') {
        if (!empty($params['resolved_time']) || !empty($params['Resolved_Time'])) {
            $resolvedTime = parse_datetime($params['resolved_time'] ?? $params['Resolved_Time']);
        }
        if ($resolvedTime === null) {
            $resolvedTime = date('Y-m-d H:i:s');
        }
        if ($ticket['status'] !== 'Closed') {
            $fields['progress_log'] .= "\n[" . date('d/m/Y H:i:s') . '] Ticket Ditutup';
        }
    }

    $nowStr = date('Y-m-d H:i:s');

    $pdo->prepare(
        'UPDATE tickets
         SET status = ?, tt_description = ?, root_cause = ?, responsibility = ?,
             problem_coordinate = ?, restoration_action = ?, progress_log = ?,
             resolved_time = ?, updated_at = ?
         WHERE id = ?'
    )->execute([
        $fields['status'],
        $fields['tt_description'],
        $fields['root_cause']         ?: null,
        $fields['responsibility']     ?: null,
        $fields['problem_coordinate'] ?: null,
        $fields['restoration_action'] ?: null,
        $fields['progress_log'],
        $resolvedTime,
        $nowStr,
        $id,
    ]);

    $updated = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
    $updated->execute([$id]);

    json_response(true, format_ticket($updated->fetch()), 'Ticket berhasil diperbarui.');
}

/**
 * DELETE – remove a ticket.
 */
function handle_delete(PDO $pdo, array $params): void
{
    $id = isset($params['id']) ? (int) $params['id'] : 0;

    // Legacy: also accept tt_customer as identifier
    if ($id === 0 && !empty($params['TT_Customer'])) {
        $stmt = $pdo->prepare('SELECT id FROM tickets WHERE tt_customer = ?');
        $stmt->execute([trim($params['TT_Customer'])]);
        $row = $stmt->fetch();
        if ($row) {
            $id = (int) $row['id'];
        }
    }

    if ($id === 0) {
        json_response(false, null, 'Ticket ID is required.', 422);
    }

    $stmt = $pdo->prepare('SELECT id FROM tickets WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        json_response(false, null, 'Ticket not found.', 404);
    }

    $pdo->prepare('DELETE FROM tickets WHERE id = ?')->execute([$id]);

    json_response(true, null, 'Ticket berhasil dihapus.');
}

// ============================================================
// Helpers
// ============================================================

/**
 * Map a raw DB row to the field names the frontend expects.
 *
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function format_ticket(array $row): array
{
    return [
        'id'                  => (int) $row['id'],
        'TT_Customer'         => $row['tt_customer'],
        'TT_TBG'              => $row['tt_tbg'],
        'TT_Description'      => $row['tt_description'],
        'Device_Segment'      => $row['device_segment'],
        'Regional'            => $row['regional'],
        'Vendor'              => $row['vendor'],
        'Segment_Problem'     => $row['segment_problem'],
        'CID'                 => $row['cid'],
        'Segment_Length'      => $row['segment_length'] ?? '',
        'Start_Time'          => format_datetime($row['start_time']),
        'Resolved_Time'       => isset($row['resolved_time']) ? format_datetime($row['resolved_time']) : '',
        'Root_Cause'          => $row['root_cause']          ?? '',
        'Responsibility'      => $row['responsibility']      ?? '',
        'Problem_Coordinate'  => $row['problem_coordinate']  ?? '',
        'Restoration_Action'  => $row['restoration_action']  ?? '',
        'Status'              => $row['status'],
        'Progress_Log'        => $row['progress_log']        ?? '',
        'created_at'          => $row['created_at'],
        'updated_at'          => $row['updated_at'],
    ];
}
