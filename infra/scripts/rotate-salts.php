<?php

/**
 * Script de rotación de WordPress Salts para el Rojas Stack.
 * Genera nuevas llaves de seguridad y las inyecta en el archivo .env.
 */

declare(strict_types=1);

$envPath = __DIR__ . '/../../.env';

if (!file_exists($envPath)) {
    echo "Error: No se encontró el archivo .env en $envPath\n";
    exit(1);
}

$keys = [
    'AUTH_KEY',
    'SECURE_AUTH_KEY',
    'LOGGED_IN_KEY',
    'NONCE_KEY',
    'AUTH_SALT',
    'SECURE_AUTH_SALT',
    'LOGGED_IN_SALT',
    'NONCE_SALT'
];

/**
 * Genera una cadena aleatoria segura.
 */
function generate_salt(int $length = 64): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
    $salt = '';
    for ($i = 0; $i < $length; $i++) {
        $salt .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $salt;
}

$envContent = file_get_contents($envPath);

foreach ($keys as $key) {
    $newSalt = generate_salt();
    // Reemplaza la línea que empieza con la llave, permitiendo comillas simples o dobles
    $pattern = "/^$key=['\"].*['\"]/m";
    $replacement = "$key='$newSalt'";
    
    if (preg_match($pattern, $envContent)) {
        $envContent = preg_replace($pattern, $replacement, $envContent);
    } else {
        // Si no existe, la añade al final
        $envContent .= "\n$replacement";
    }
}

file_put_contents($envPath, $envContent);

echo "✅ WordPress Salts rotados exitosamente en el archivo .env\n";
