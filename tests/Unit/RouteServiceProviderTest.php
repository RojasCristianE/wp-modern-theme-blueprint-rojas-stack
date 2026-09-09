<?php

declare(strict_types=1);

use RojasStack\Providers\RouteServiceProvider;
use function Brain\Monkey\Functions\when;

/*
 * Tests del router genérico de rutas virtuales.
 * Contrato clave (spec §12.1): al interceptar una ruta, la respuesta se
 * re-marca como "found" — status 200 + is_404 en false — porque WP ya la
 * resolvió como 404 al no existir la página en la DB.
 */

/**
 * Stub mínimo de WP_Query: sin WordPress real la clase no existe y el
 * router la exige en su guard `instanceof`. Soloporta el flag is_404.
 */
if (!class_exists('WP_Query')) {
    class WP_Query
    {
        public bool $is_404 = true;
    }
}

afterEach(function () {
    $_SERVER['REQUEST_URI'] = null;
    unset($GLOBALS['wp_query']);
});

it('registra el dispatch en template_redirect', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new RouteServiceProvider())->register();

    expect($hooks)->toHaveKey('template_redirect');
});

it('ejecuta el callback de la ruta que coincide y re-marca la respuesta como found', function () {
    $status = null;
    $hit = false;

    when('status_header')->alias(function (int $code) use (&$status) {
        $status = $code;
    });
    when('nocache_headers')->justReturn();

    // Doble de $wp_query: el router solo asigna el flag is_404.
    $GLOBALS['wp_query'] = new WP_Query();

    $_SERVER['REQUEST_URI'] = '/blog?utm_source=test';

    $router = new RouteServiceProvider();
    $router->add_route('/blog', function () use (&$hit) {
        $hit = true;
    });

    // terminate() se redefine con Patchwork para no matar el proceso de test.
    \Patchwork\redefine([$router, 'terminate'], function (): never {
        throw new RuntimeException('TERMINATED');
    });

    try {
        $router->dispatch_routes();
        $this->fail('dispatch_routes() debería terminar la ejecución tras renderizar');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('TERMINATED');
    }

    expect($hit)->toBeTrue()
        ->and($status)->toBe(200)
        ->and($GLOBALS['wp_query']->is_404)->toBeFalse();
});

it('normaliza el trailing slash y el query string al comparar rutas', function () {
    $hit = false;

    when('status_header')->justReturn();
    when('nocache_headers')->justReturn();

    $GLOBALS['wp_query'] = new WP_Query();

    $_SERVER['REQUEST_URI'] = '/blog/?pagina=2';

    $router = new RouteServiceProvider();
    $router->add_route('/blog', function () use (&$hit) {
        $hit = true;
    });

    \Patchwork\redefine([$router, 'terminate'], function (): never {
        throw new RuntimeException('TERMINATED');
    });

    try {
        $router->dispatch_routes();
    } catch (RuntimeException) {
        // esperado
    }

    expect($hit)->toBeTrue();
});

it('despacha callables sobre instancias configuradas (FormController)', function () {
    when('status_header')->justReturn();
    when('nocache_headers')->justReturn();

    $GLOBALS['wp_query'] = new WP_Query();

    $_SERVER['REQUEST_URI'] = '/contacto';

    $form = new class {
        public bool $rendered = false;

        public function render_page(): void
        {
            $this->rendered = true;
        }
    };

    $router = new RouteServiceProvider();
    $router->add_route('/contacto', [$form, 'render_page']);

    \Patchwork\redefine([$router, 'terminate'], function (): never {
        throw new RuntimeException('TERMINATED');
    });

    try {
        $router->dispatch_routes();
    } catch (RuntimeException) {
        // esperado
    }

    expect($form->rendered)->toBeTrue();
});

it('matchea patrones con placeholders y pasa los segmentos al callback', function () {
    when('status_header')->justReturn();
    when('nocache_headers')->justReturn();

    $GLOBALS['wp_query'] = new WP_Query();

    $_SERVER['REQUEST_URI'] = '/blog/page/2';

    $received = 'NO_CALLED';
    $router = new RouteServiceProvider();
    $router->add_route('/blog/page/{paged}', function (int $paged) use (&$received) {
        $received = $paged;
    });

    \Patchwork\redefine([$router, 'terminate'], function (): never {
        throw new RuntimeException('TERMINATED');
    });

    try {
        $router->dispatch_routes();
    } catch (RuntimeException) {
        // esperado
    }

    expect($received)->toBe(2);
});

it('no matchea el patrón cuando falta el segmento variable', function () {
    $_SERVER['REQUEST_URI'] = '/blog/page/';

    $router = new RouteServiceProvider();
    $router->add_route('/blog/page/{paged}', fn () => throw new RuntimeException('no debería ejecutarse'));

    $router->dispatch_routes();

    expect(true)->toBe(true);
});

it('no toca nada si ninguna ruta coincide', function () {
    $_SERVER['REQUEST_URI'] = '/pagina-inexistente';

    $router = new RouteServiceProvider();
    $router->add_route('/blog', fn () => throw new RuntimeException('no debería ejecutarse'));

    // Sin match: no hay status, no hay exit — la función retorna normal.
    $router->dispatch_routes();

    expect(true)->toBe(true);
});
