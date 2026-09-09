<?php

declare(strict_types=1);

use RojasStack\Controllers\DesignSystemController;
use function Brain\Monkey\Functions\when;

it('renderiza el catálogo de componentes con título', function () {
    when('__')->returnArg();

    /** @var array<int|string, mixed> $captured */
    $captured = [];

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$captured) {
        $captured = [$template, $context];
    });

    (new DesignSystemController())->render_page();

    expect($captured[0])->toBe('design-system.twig')
        ->and($captured[1]['title'])->toBe('Design System');
});
