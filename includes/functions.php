<?php
/**
 * Helper / utility functions for NOC Trouble Ticket System.
 */

/**
 * Send a JSON response and terminate script execution.
 *
 * @param bool  $success
 * @param mixed $data
 * @param string $message
 * @param int   $httpStatus
 * @return never
 */
function json_response(bool $success, $data = null, string $message = '', int $httpStatus = 200): never
{
    http_response_code($httpStatus);
    header('Content-Type: application/json; charset=utf-8');

    $payload = ['success' => $success];
    if ($message !== '') {
        $payload['message'] = $message;
    }
    if ($data !== null) {
        $payload['data'] = $data;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Return a sanitized string value from $_REQUEST or a default.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function input_string(string $key, string $default = ''): string
{
    $value = $_REQUEST[$key] ?? $default;
    return trim((string) $value);
}

/**
 * Validate that required fields are present and non-empty.
 *
 * @param array<string> $required  List of field names.
 * @param array<string,string> $data  Associative array of field => value.
 * @return string  First validation error message, or empty string on success.
 */
function validate_required(array $required, array $data): string
{
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            return "Field '{$field}' is required.";
        }
    }
    return '';
}

/**
 * Set CORS headers to allow cross-origin API access.
 * Adjust the allowed-origin pattern for your deployment.
 */
function set_cors_headers(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Parse a datetime string into MySQL DATETIME format (YYYY-MM-DD HH:MM:SS).
 * Returns null if the string cannot be parsed.
 *
 * @param string $value
 * @return string|null
 */
function parse_datetime(string $value): ?string
{
    if (trim($value) === '') {
        return null;
    }

    $ts = strtotime($value);
    if ($ts === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $ts);
}

/**
 * Format a MySQL DATETIME string for display.
 *
 * @param string|null $value
 * @return string
 */
function format_datetime(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    $ts = strtotime($value);
    if ($ts === false) {
        return $value;
    }

    return date('d/m/Y H:i:s', $ts);
}
