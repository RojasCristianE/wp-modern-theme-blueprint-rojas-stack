<?php

namespace RojasStack\Providers;

use Closure;
use WP_Query;

/**
 * RouteServiceProvider
 *
 * Router genérico para rutas virtuales que no requieren una página creada
 * en la base de datos de WordPress. No conoce módulos: ThemeSetup registra
 * las rutas de cada feature habilitado vía add_route() (spec §5).
 * Permite definir la estructura del sitio 100% desde el código.
 *
 * Nota (spec §12.1): el router intercepta en template_redirect, DESPUÉS de
 * que WP ya resolvió la query. Como la ruta no existe en la DB, WP marcó
 * la petición como 404 (is_404 + status header); al servir contenido real
 * re-marcamos la respuesta como 200 vía serve_as_found().
 */
class RouteServiceProvider
{
    /**
     * Mapa de rutas: 'slug' => callback
     *
     * @var array<string, callable> $routes
     */
    private array $routes = [];

    /**
     * Registra una ruta virtual.
     *
     * @param array{0: class-string|object, 1: string}|callable $callback
     *        [ControllerClass, method], [instancia, method] o un Closure
     *        (p.ej. FormController por formulario).
     */
    public function add_route(string $path, array|callable $callback): void
    {
        $this->routes[$path] = $callback;
    }

    public function register(): void
    {
        add_action('template_redirect', [$this, 'dispatch_routes']);
    }

    /**
     * Intercepta la petición y delega al controlador si la ruta coincide.
     */
    public function dispatch_routes(): void
    {
        $current_path = strtok($_SERVER['REQUEST_URI'], '?');
        $current_path = rtrim($current_path, '/');

        // Normalizamos el path vacío para la Home si fuera necesario
        if (empty($current_path)) $current_path = '/';

        foreach ($this->routes as $path => $callback) {
            if ($current_path === $path) {
                // Ruta encontrada: WP marcó 404 (no existe en la DB) porque
                // interceptamos después de la resolución de la query. Servimos
                // contenido real => forzamos 200 y limpiamos el flag is_404
                // (título "Page not found" y meta robots noindex dependen de él).
                $this->serve_as_found();

                // Callable sobre instancia ya configurada (FormController)
                if ($callback instanceof Closure) {
                    $callback();
                    $this->terminate(); // Detenemos WordPress después de renderizar nuestra ruta virtual
                }

                // Par [ControllerClass|instancia, method]
                $controller = is_object($callback[0]) ? $callback[0] : new $callback[0]();
                $method = $callback[1];

                $controller->$method();
                $this->terminate(); // Detenemos WordPress después de renderizar nuestra ruta virtual
            }
        }
    }

    /**
     * Re-marca la respuesta como "found" (200): status header + flag is_404.
     */
    private function serve_as_found(): void
    {
        status_header(200);
        nocache_headers();

        global $wp_query;
        if ($wp_query instanceof WP_Query) {
            $wp_query->is_404 = false;
        }
    }

    /**
     * Detiene WordPress tras renderizar la ruta virtual.
     * Método aparte para poder redefinirlo con Patchwork en los tests.
     */
    private function terminate(): never
    {
        exit;
    }
}
