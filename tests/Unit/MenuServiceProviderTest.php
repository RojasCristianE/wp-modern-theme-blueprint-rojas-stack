<?php

declare(strict_types=1);

use RojasStack\Providers\MenuServiceProvider;
use function Brain\Monkey\Functions\when;

/*
 * Highlight de sección activa (item.current): match por prefijo de path.
 * /blog ilumina también /blog/page/2/ y los singles de la sección; Home
 * solo matchea la raíz exacta para no robarle el highlight al resto.
 *
 * La clase global WP no existe en la suite: stub mínimo para que
 * $GLOBALS['wp'] instanceof \WP sea verdadero.
 */

if (! class_exists('WP')) {
    class WP
    {
        public string $request = '';
    }
}

beforeEach(function () {
    when('__')->alias(static fn (string $text): string => $text);
    when('home_url')->alias(
        static fn (string $path = ''): string => 'http://theme.test/' . ltrim($path, '/'),
    );
    when('add_filter')->justReturn(null);
});

/**
 * Corre la inyección de contexto para un request path y devuelve el flag
 * `current` de cada item del menú (mismo orden que la definición).
 *
 * @return list<bool>
 */
function menu_current_flags(string $request_path, bool $with_wp = true): array
{
    if ($with_wp) {
        $wp           = new WP();
        $wp->request  = $request_path;
        $GLOBALS['wp'] = $wp;
    } else {
        unset($GLOBALS['wp']);
    }

    $context = (new MenuServiceProvider())->inject_menu_to_context([]);

    $menu = $context['menu'] ?? null;
    assert($menu instanceof stdClass);
    $items = $menu->items;
    assert(is_array($items));

    return array_values(array_map(
        static fn (mixed $item): bool => (bool) (is_object($item) ? ($item->current ?? false) : false),
        $items,
    ));
}

it('ilumina solo Inicio en la raíz', function () {
    expect(menu_current_flags('/'))->toBe([true, false, false]);
});

it('ilumina Blog en la sección completa: paginación y singles', function (string $path) {
    expect(menu_current_flags($path))->toBe([false, true, false]);
})->with([
    '/blog',
    '/blog/',
    '/blog/page/2/',
    '/blog/hola-mundo/',
]);

it('ilumina Contacto en su sección', function () {
    expect(menu_current_flags('/contacto/'))->toBe([false, false, true]);
});

it('no ilumina nada fuera de las secciones del menú', function () {
    expect(menu_current_flags('/podcast/episodio-1/'))->toBe([false, false, false]);
});

it('path vacío (CLI o home real, donde $wp->request es ""): solo Inicio', function () {
    expect(menu_current_flags('', with_wp: false))->toBe([true, false, false]);
});
