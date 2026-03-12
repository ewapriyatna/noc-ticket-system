<?php
/**
 * Database configuration for NOC Trouble Ticket System.
 * Copy this file to config.php and adjust the values to match your environment.
 */

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'noc_ticket_system');
define('DB_USER',    'noc_user');
define('DB_PASS',    'your_secure_password_here');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME',    'NOC Trouble Ticket System');
define('APP_VERSION', '2.0.0');

// Error reporting (set to false in production)
define('APP_DEBUG', true);
