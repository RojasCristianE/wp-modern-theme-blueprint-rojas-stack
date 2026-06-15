<?php

/**
 * Rojas Stack: Modern Theme Architecture for WordPress
 * 
 * Este archivo actúa como el punto de entrada (Bootstrap) del tema.
 * Se encarga de cargar el autoloader de Composer, inicializar variables de entorno
 * y delegar la configuración a las clases de la carpeta app/.
 * 
 * @package RojasStack
 * @author Cristian Rojas (El Arquitecto)
 */

declare(strict_types=1);

// 1. Cargar el Autoloader de Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Cargar variables de entorno (.env)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// 2.1 Habilitar Herramientas de Debugging (Ignition & Clockwork)
if (isset($_ENV['WP_ENV']) && $_ENV['WP_ENV'] === 'development') {
    // Silenciar depreciaciones en PHP 8.5+ manualmente antes de Ignition
    set_error_handler(function ($level, $message, $file, $line) {
        if ($level & (E_DEPRECATED | E_USER_DEPRECATED)) {
            return true; // Detener propagación
        }
        return false; // Seguir con el manejador de Ignition
    });

    // Spatie Ignition: Errores Visuales Modernos
    if (class_exists(\Spatie\Ignition\Ignition::class)) {
        \Spatie\Ignition\Ignition::make()
            ->shouldDisplayException(true)
            ->register();
        
        // En PHP 8.5+, Ignition captura depreciaciones de librerías core. 
        // Restauramos el manejador previo para que solo las excepciones fatales usen Ignition.
        restore_error_handler();
    }

    // Clockwork: Profiling y Debugging en Consola/Browser Extension
    if (class_exists(\Clockwork\Support\Vanilla\Clockwork::class)) {
        \Clockwork\Support\Vanilla\Clockwork::init([
            'register_helpers' => true,
        ]);
    }

    // Forzar el silenciado de depreciaciones al final de la inicialización de debug
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

// 3. Inicializar Timber (Motor de plantillas Twig)
Timber\Timber::init();

/**
 * Configuración de rutas para Twig
 */
Timber\Timber::$dirname = ['resources/views'];

// 4. Inicializar Carbon Fields (Metadatos Code-First)
add_action('after_setup_theme', function () {
    $current_error_level = error_reporting();
    error_reporting(0); // Silenciar temporalmente para evitar depreciaciones en PHP 8.5
    \Carbon_Fields\Carbon_Fields::boot();
    error_reporting($current_error_level); // Restaurar nivel anterior
});

// 5. Iniciar la orquestación del tema
// Delegamos la configuración a un Service Provider o clase de Setup
if (class_exists('RojasStack\Setup\ThemeSetup')) {
    new RojasStack\Setup\ThemeSetup();
}

// Nota: No cerramos la etiqueta PHP para evitar espacios en blanco accidentales.
