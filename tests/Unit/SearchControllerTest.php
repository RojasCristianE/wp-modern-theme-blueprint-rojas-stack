<?php

declare(strict_types=1);

use RojasStack\Controllers\SearchController;
use Brain\Monkey\Functions;
use function Brain\Monkey\Functions\when;

/*
 * Tests del endpoint de búsqueda en vivo (spec §9.3, contrato de fragmentos).
 * La petición HTMX llega a admin-ajax?action=rs_live_search con el término
 * en 's'; el controller responde SOLO partials/search-results.twig.
 */

afterEach(function () {
    $_GET = [];
});

it('registra el endpoint AJAX para visitantes y usuarios logueados', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new SearchController())->register();

    expect($hooks)->toHaveKeys([
        'wp_ajax_rs_live_search',
        'wp_ajax_nopriv_rs_live_search',
    ])
        ->and($hooks['wp_ajax_rs_live_search'][1])->toBe('render_fragment');
});

it('no consulta la DB si el término está vacío', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_text_field')->returnArg();
    // En WordPress real wp_die() TERMINA la ejecución (mismo patrón que
    // FormControllerTest): el stub lanza para que el flujo no continúe
    // hacia Timber::context()/get_posts() tras el early-return.
    Functions\expect('wp_die')->once()->andReturnUsing(function () {
        throw new RuntimeException('WP_DIE');
    });

    $rendered = null;
    $postsArgs = 'NO_CALLED';
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });

    $_GET['s'] = '';

    try {
        (new SearchController())->render_fragment();
        $this->fail('wp_die() debería haber terminado la ejecución');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('WP_DIE');
    }

    expect($postsArgs)->toBe('NO_CALLED', 'no debería consultarse la DB con término vacío')
        ->and($rendered[0])->toBe('partials/search-results.twig')
        ->and($rendered[1]['posts'])->toBe([])
        ->and($rendered[1]['search_query'])->toBe('');
});

it('busca posts y renderiza el fragmento con el término saneado', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_text_field')->alias(fn (string $s) => trim($s));
    when('__')->returnArg();
    Functions\expect('wp_die')->once();

    $postsArgs = null;
    $rendered = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return ['POST_A', 'POST_B'];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });

    $_GET['s'] = '  podman  ';

    (new SearchController())->render_fragment();

    expect($postsArgs['s'])->toBe('podman')
        ->and($postsArgs['posts_per_page'])->toBe(5)
        ->and($rendered[0])->toBe('partials/search-results.twig')
        ->and($rendered[1]['search_query'])->toBe('podman');
});
