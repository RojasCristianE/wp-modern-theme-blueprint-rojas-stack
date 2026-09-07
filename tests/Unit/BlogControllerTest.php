<?php

declare(strict_types=1);

use RojasStack\Controllers\BlogController;
use function Brain\Monkey\Functions\when;

it('renderiza blog.twig con título y posts en el contexto', function () {
    when('__')->returnArg();

    $captured = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => ['site_url' => 'http://site.test']);
    \Patchwork\redefine('Timber\Timber::get_posts', fn (): array => ['POST_A', 'POST_B']);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$captured) {
        $captured = [$template, $context];
    });

    (new BlogController())->render_page();

    expect($captured[0])->toBe('blog.twig')
        ->and($captured[1]['title'])->toBe('Blog')
        ->and($captured[1]['posts'])->toBe(['POST_A', 'POST_B'])
        ->and($captured[1]['site_url'])->toBe('http://site.test');
});

it('register() es un no-op seguro', function () {
    (new BlogController())->register();

    // register() no registra hooks todavía: no debe lanzar nada.
    expect(true)->toBeTrue();
});
