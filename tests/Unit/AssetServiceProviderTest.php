<?php

declare(strict_types=1);

use RojasStack\Providers\AssetServiceProvider;
use function Brain\Monkey\Functions\when;

/*
 * Tests de integración: el provider delega en kucrut/vite-for-wp REAL
 * (no se redefine la librería). Se mockea únicamente la superficie de WP
 * con Brain Monkey y se usa el manifest fixture de tests/fixtures/theme.
 *
 * Los $GLOBALS de captura se leen/escriben con assert(is_array()) para
 * cumplir L10 (los elementos de $GLOBALS son mixed para el análisis).
 */

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', FIXTURE_THEME);
}

const DEV_SERVER_FIXTURE = FIXTURE_THEME . '/dist/vite-dev-server.json';

beforeEach(function () {
    // Superficie WP usada por vite-for-wp (params tipados: L10 prohíbe
    // castear mixed)
    when('get_template_directory')->justReturn(FIXTURE_THEME);
    when('wp_json_file_decode')->alias(fn (string $path) => json_decode((string) file_get_contents($path)));
    when('wp_normalize_path')->alias(fn (string $path): string => str_replace('\\', '/', $path));
    when('wp_parse_args')->alias(fn (array $args, array $defaults): array => array_merge($defaults, $args));
    when('apply_filters')->alias(fn (mixed ...$args): mixed => $args[1]);
    when('content_url')->alias(fn (string $path): string => 'http://theme.test/wp-content/' . ltrim($path, '/'));
    when('untrailingslashit')->alias(fn (string $value): string => rtrim($value, '/'));
    when('add_filter')->justReturn(null);
    when('wp_script_is')->justReturn(false);

    // Registro/encolado capturados
    $GLOBALS['vftw_registered_scripts'] = [];
    $GLOBALS['vftw_registered_styles'] = [];
    $GLOBALS['vftw_enqueued'] = [];

    when('wp_register_script')->alias(function (string $handle, string $src): bool {
        $scripts = $GLOBALS['vftw_registered_scripts'];
        assert(is_array($scripts));
        $scripts[$handle] = $src;
        $GLOBALS['vftw_registered_scripts'] = $scripts;

        return true;
    });
    when('wp_register_style')->alias(function (string $handle, string $src): bool {
        $styles = $GLOBALS['vftw_registered_styles'];
        assert(is_array($styles));
        $styles[$handle] = $src;
        $GLOBALS['vftw_registered_styles'] = $styles;

        return true;
    });
    when('wp_enqueue_script')->alias(function (string $handle): string {
        $enqueued = $GLOBALS['vftw_enqueued'];
        assert(is_array($enqueued));
        $enqueued[] = $handle;
        $GLOBALS['vftw_enqueued'] = $enqueued;

        return $handle;
    });
    when('wp_enqueue_style')->alias(function (string $handle): string {
        $enqueued = $GLOBALS['vftw_enqueued'];
        assert(is_array($enqueued));
        $enqueued[] = $handle;
        $GLOBALS['vftw_enqueued'] = $enqueued;

        return $handle;
    });
});

afterEach(function () {
    if (is_file(DEV_SERVER_FIXTURE)) {
        unlink(DEV_SERVER_FIXTURE);
    }
});

it('registra el hook wp_enqueue_scripts', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new AssetServiceProvider())->register();

    expect($hooks)->toHaveKey('wp_enqueue_scripts');

    $hook = $hooks['wp_enqueue_scripts'] ?? null;
    expect($hook)->toBeArray()
        ->and(is_array($hook) ? $hook[1] : null)->toBe('enqueue_assets');
});

it('carga el bundle de producción desde dist/manifest.json con su CSS', function () {
    (new AssetServiceProvider())->enqueue_assets();

    /** @var array<string, string> $scripts */
    $scripts = $GLOBALS['vftw_registered_scripts'];
    /** @var array<string, string> $styles */
    $styles = $GLOBALS['vftw_registered_styles'];
    /** @var array<int, string> $enqueued */
    $enqueued = $GLOBALS['vftw_enqueued'];

    expect($scripts)->toHaveKey('rojas-main')
        ->and($scripts['rojas-main'])->toBe('http://theme.test/wp-content/dist/assets/main-abc123.js')
        ->and($styles)->not->toBeEmpty()
        ->and(array_values($styles)[0])->toContain('assets/main-def456.css')
        ->and($enqueued)->toContain('rojas-main');
});

it('sirve los assets desde el dev server mientras corre bun run dev', function () {
    // Lo que el plugin dev_server() de @kucrut/vite-for-wp escribe en dist/
    file_put_contents(DEV_SERVER_FIXTURE, (string) json_encode([
        'base'    => '/',
        'origin'  => 'http://localhost:5173',
        'port'    => 5173,
        'plugins' => [],
    ]));

    (new AssetServiceProvider())->enqueue_assets();

    /** @var array<string, string> $scripts */
    $scripts = $GLOBALS['vftw_registered_scripts'];

    // Cliente Vite + entry principal apuntando al dev server (HMR)
    expect($scripts)->not->toBeEmpty()
        ->and(array_filter($scripts, fn (string $src): bool => str_contains($src, '@vite/client')))->not->toBeEmpty()
        ->and($scripts['rojas-main'])->toBe('http://localhost:5173/resources/scripts/main.js');
});
