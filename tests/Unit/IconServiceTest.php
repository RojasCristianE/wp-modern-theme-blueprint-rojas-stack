<?php

declare(strict_types=1);

use RojasStack\Providers\IconServiceProvider;
use RojasStack\Services\IconService;
use function Brain\Monkey\Functions\when;

/*
 * Tests del servicio de iconos (spec §12.1, entrada 8). Usan los SVGs
 * reales sincronizados en resources/icons/ — el contrato es el árbol
 * versionado, no un fixture paralelo.
 *
 * Nota: `data-rs-icon` lo añade la envoltura components/icon.twig (via
 * attrs), no el servicio — aquí se pasa explícito para probar el
 * passthrough de atributos.
 */

beforeEach(function () {
    when('esc_attr')->alias(static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES));
});

it('renderiza un icono del set primario con clase y aria-hidden', function () {
    $svg = IconService::render('search');

    expect($svg)->toStartWith('<svg')
        ->and($svg)->toContain('class="w-5 h-5"')
        ->and($svg)->toContain('aria-hidden="true"')
        ->and($svg)->toContain('viewBox="0 0 24 24"');
});

it('resuelve el prefijo set:name', function () {
    $mail = IconService::render('material:mail');
    $ring = IconService::render('spinner:180-ring');

    expect($mail)->toStartWith('<svg')
        ->and($mail)->not->toContain('animation') // material round: estático
        ->and($ring)->toStartWith('<svg')
        ->and($ring)->toContain('animation'); // spinner: CSS embebida
});

it('hace fallback en cadena entre sets', function () {
    // 'arrow-close-down' solo existe en line-md; forzar material debe
    // caer al set primario (misma geometría Material).
    $svg = IconService::render('material:arrow-close-down');

    expect($svg)->toStartWith('<svg')
        ->and($svg)->toContain('stroke'); // line-md es trazo, material round es fill
});

it('aplica clases y atributos extra', function () {
    $svg = IconService::render('search', ['class' => 'w-8 h-8', 'title' => 'Buscar', 'data-rs-icon' => 'search']);

    expect($svg)->toContain('class="w-8 h-8"')
        ->and($svg)->toContain('title="Buscar"')
        ->and($svg)->toContain('data-rs-icon="search"')
        ->and($svg)->toContain('aria-hidden="true"');
});

it('descarta atributos vacíos o nulos', function () {
    $svg = IconService::render('search', ['title' => '', 'label' => null]);

    expect($svg)->not->toContain('title=')
        ->and($svg)->not->toContain('label=');
});

it('devuelve vacío para nombres desconocidos o inseguros', function () {
    expect(IconService::render('no-existe'))->toBe('')
        ->and(IconService::render('../composer'))->toBe('')
        ->and(IconService::render('material:../../composer'))->toBe('')
        ->and(IconService::render('set-desconocido:search'))->toBe('')
        ->and(IconService::render(''))->toBe('');
});

it('el provider registra rs_icon() en Twig y pasa el nombre intacto', function () {
    $captured = [];

    when('add_filter')->alias(function (string $hook, callable $cb) use (&$captured): bool {
        $captured[$hook] = $cb;

        return true;
    });

    $provider = new IconServiceProvider();
    $provider->register();

    expect($captured)->toHaveKey('timber/twig');

    $twig = new \Twig\Environment(new \Twig\Loader\ArrayLoader());
    $returned = $captured['timber/twig']($twig);

    expect($returned)->toBe($twig);

    $function = $twig->getFunction('rs_icon');
    assert($function instanceof \Twig\TwigFunction);
    $callable = $function->getCallable();
    assert(is_callable($callable));

    expect($function->getName())->toBe('rs_icon')
        ->and($callable('search'))->toStartWith('<svg')
        // Firma tolerante: entrada no-string no revienta.
        ->and($callable(42))->toBe('');
});
