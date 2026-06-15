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

// 3. Inicializar Timber (Motor de plantillas Twig)
Timber\Timber::init();

/**
 * Configuración de rutas para Twig
 */
Timber\Timber::$dirname = ['resources/views'];

// 4. Inicializar Carbon Fields (Metadatos Code-First)
add_action('after_setup_theme', function () {
    \Carbon_Fields\Carbon_Fields::boot();
});

// 5. Iniciar la orquestación del tema
// Delegamos la configuración a un Service Provider o clase de Setup
if (class_exists('RojasStack\Setup\ThemeSetup')) {
    new RojasStack\Setup\ThemeSetup();
}

// Nota: No cerramos la etiqueta PHP para evitar espacios en blanco accidentales.
