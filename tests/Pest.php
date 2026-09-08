<?php

declare(strict_types=1);

/*
 * Configuración global de la suite (Pest).
 *
 * Cada test arranca con Brain Monkey activo (stubs de WP) y termina
 * con Patchwork, Mockery y superglobales restaurados.
 *
 * Constantes compartidas se definen aquí UNA vez: duplicarlas entre
 * archivos de test genera warnings de "already defined" (error en PHP 9).
 */

use Brain\Monkey;

const FIXTURE_THEME = __DIR__ . '/fixtures/theme';
const THEME_URI     = 'http://theme.test/wp-content/themes/blueprint';

// Stub mínimo de WP_Error para clientes HTTP (MeilisearchClient): la
// suite no carga WordPress real. Los helpers asociados (is_wp_error,
// wp_remote_retrieve_body, wp_remote_retrieve_response_code) se stubbean
// con Brain Monkey en los tests que los necesitan — una función real
// aquí quedaría congelada ANTES de Patchwork (DefinedTooEarly).
if (! class_exists('WP_Error')) {
    class WP_Error
    {
        public function __construct(
            private readonly string $code = '',
            private readonly string $message = '',
        ) {
        }

        public function get_error_message(): string
        {
            return $this->message;
        }
    }
}

beforeEach(function () {
    Monkey\setUp();
});

afterEach(function () {
    Monkey\tearDown();

    // Redundante con tearDown() pero explícito: deshace cualquier
    // redefinición de Patchwork (Timber\Timber::* en los tests).
    if (function_exists('Patchwork\\restoreAll')) {
        \Patchwork\restoreAll();
    }

    \Mockery::close();

    $_POST = [];
});
