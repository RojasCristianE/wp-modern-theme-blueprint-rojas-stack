<?php

/**
 * wp-config.php Blueprint para Rojas Stack.
 * 
 * Este archivo debe moverse a la raíz de la instalación de WordPress.
 * Mapea las variables del archivo .env a las constantes globales de WP.
 */

require_once __DIR__ . '/wp-content/themes/wp-modern-theme-blueprint/vendor/autoload.php';

if (file_exists(__DIR__ . '/wp-content/themes/wp-modern-theme-blueprint/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/wp-content/themes/wp-modern-theme-blueprint/');
    $dotenv->safeLoad();
}

// DB Settings
define('DB_NAME', $_ENV['DB_NAME'] ?? 'database_name_here');
define('DB_USER', $_ENV['DB_USER'] ?? 'username_here');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'password_here');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Salts
define('AUTH_KEY',         $_ENV['AUTH_KEY']);
define('SECURE_AUTH_KEY',  $_ENV['SECURE_AUTH_KEY']);
define('LOGGED_IN_KEY',    $_ENV['LOGGED_IN_KEY']);
define('NONCE_KEY',        $_ENV['NONCE_KEY']);
define('AUTH_SALT',        $_ENV['AUTH_SALT']);
define('SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT']);
define('LOGGED_IN_SALT',   $_ENV['LOGGED_IN_SALT']);
define('NONCE_SALT',       $_ENV['NONCE_SALT']);

$table_prefix = 'wp_';

define('WP_DEBUG', $_ENV['WP_ENV'] === 'development');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
