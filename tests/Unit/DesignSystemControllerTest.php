<?php

declare(strict_types=1);

use RojasStack\Controllers\DesignSystemController;
use function Brain\Monkey\Functions\when;

it('renderiza el catálogo de componentes con título', function () {
    when('__')->returnArg();

    $template = '';
    $context  = [];

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $t, array $c) use (&$template, &$context) {
        $template = $t;
        $context  = $c;
    });

    (new DesignSystemController())->render_page();

    expect($template)->toBe('design-system.twig')
        ->and($context['title'] ?? null)->toBe('Design System');
});
