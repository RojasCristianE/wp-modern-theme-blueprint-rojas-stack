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
 *
 * Sobre el fallback en cadena: los dos sets (heroicons outline/solid)
 * son espejos completos (mismos 324 nombres), así que no existe icono
 * que caiga de uno al otro — la cadena queda cubierta por los tests de
 * set explícito y de nombres desconocidos.
 */

beforeEach(function () {
    when('esc_attr')->alias(static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES));
});

it('renderiza un icono del set primario con clase y aria-hidden', function () {
    $svg = IconService::render('magnifying-glass');

    expect($svg)->toStartWith('<svg')
        ->and($svg)->toContain('class="w-5 h-5"')
        ->and($svg)->toContain('aria-hidden="true"')
        ->and($svg)->toContain('viewBox="0 0 24 24"')
        ->and($svg)->toContain('stroke="currentColor"'); // outline: trazo heredado
});

it('resuelve el prefijo set:name (solid es fill, outline es stroke)', function () {
    $outline = IconService::render('arrow-right');
    $solid = IconService::render('heroicons-solid:check-circle');

    expect($outline)->toContain('stroke-width')
        ->and($solid)->toStartWith('<svg')
        ->and($solid)->toContain('fill="currentColor"')
        ->and($solid)->not->toContain('stroke-width');
});

it('aplica clases y atributos extra', function () {
    $svg = IconService::render('magnifying-glass', ['class' => 'w-8 h-8', 'title' => 'Buscar', 'data-rs-icon' => 'magnifying-glass']);

    expect($svg)->toContain('class="w-8 h-8"')
        ->and($svg)->toContain('title="Buscar"')
        ->and($svg)->toContain('data-rs-icon="magnifying-glass"')
        ->and($svg)->toContain('aria-hidden="true"');
});

it('descarta atributos vacíos o nulos', function () {
    $svg = IconService::render('arrow-right', ['title' => '', 'label' => null]);

    expect($svg)->not->toContain('title=')
        ->and($svg)->not->toContain('label=');
});

it('devuelve vacío para nombres desconocidos o inseguros', function () {
    expect(IconService::render('no-existe'))->toBe('')
        ->and(IconService::render('../composer'))->toBe('')
        ->and(IconService::render('heroicons-solid:../../composer'))->toBe('')
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
        ->and($callable('magnifying-glass'))->toStartWith('<svg')
        // Firma tolerante: entrada no-string no revienta.
        ->and($callable(42))->toBe('');
});
