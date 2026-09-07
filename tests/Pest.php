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
