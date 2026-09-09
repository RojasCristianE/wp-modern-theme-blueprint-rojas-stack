<?php

declare(strict_types=1);

use RojasStack\Controllers\BlogController;
use function Brain\Monkey\Functions\when;

// render_page() resuelve la base bonita del contrato HTMX en CADA
// invocación: stub local explícito en vez de depender de fugas entre
// archivos de test (el orden alfabético las hacía frágiles).
beforeEach(function () {
    when('home_url')->alias(fn (string $p = '') => 'http://theme.test' . $p);
});

it('renderiza blog.twig con título y posts en el contexto', function () {
    when('__')->returnArg();
    when('get_query_var')->justReturn(0); // sin ?paged= → página 1

    $captured = null;
    $postsArgs = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => ['site_url' => 'http://site.test']);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return ['POST_A', 'POST_B'];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$captured) {
        $captured = [$template, $context];
    });

    (new BlogController())->render_page();

    expect($captured[0])->toBe('blog.twig')
        ->and($captured[1]['title'])->toBe('Blog')
        ->and($captured[1]['posts'])->toBe(['POST_A', 'POST_B'])
        ->and($captured[1]['site_url'])->toBe('http://site.test')
        ->and($postsArgs['posts_per_page'])->toBe(6)
        ->and($postsArgs['paged'])->toBe(1);
});

it('respeta posts_per_page de la configuración del módulo blog', function () {
    when('__')->returnArg();
    when('get_query_var')->justReturn(0);

    $postsArgs = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });
    \Patchwork\redefine('Timber\Timber::render', fn (string $template, array $context) => null);

    (new BlogController(['posts_per_page' => 5]))->render_page();

    expect($postsArgs['posts_per_page'])->toBe(5);
});

it('propaga la página actual del query a los args de posts (paginación)', function () {
    when('__')->returnArg();
    when('get_query_var')->alias(fn (string $var) => $var === 'paged' ? 3 : 0);

    $postsArgs = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });
    \Patchwork\redefine('Timber\Timber::render', fn (string $template, array $context) => null);

    (new BlogController())->render_page();

    expect($postsArgs['paged'])->toBe(3);
});

it('usa la página explícita de la ruta virtual y expone el contrato HTMX', function () {
    when('__')->returnArg();
    when('get_query_var')->justReturn(0);

    $captured  = null;
    $postsArgs = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$captured) {
        $captured = [$template, $context];
    });

    (new BlogController())->render_page(2);

    expect($postsArgs['paged'])->toBe(2)
        ->and($captured[1]['archive_term'])->toBe('')
        ->and($captured[1]['htmx_base'])->toBe('http://theme.test/blog/');
});

it('register() es un no-op seguro', function () {
    (new BlogController())->register();

    // register() no registra hooks todavía: no debe lanzar nada.
    expect(true)->toBe(true);
});
