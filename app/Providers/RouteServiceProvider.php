<?php

declare(strict_types=1);

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
     * Mapa de rutas: 'slug'|'patrón' => callback
     *
     * @var array<string, array{0: class-string|object, 1: string}|callable> $routes
     */
    private array $routes = [];

    /**
     * Registra una ruta virtual.
     *
     * $path puede ser un slug exacto ('/blog') o un patrón con
     * placeholders {param} que capturan 1+ caracteres sin '/':
     * '/blog/page/{paged}'. Cada segmento capturado llega al callback
     * como argumento posicional (string — el método destino coacciona
     * al tipo declarado; los controllers del stack no usan strict_types).
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
        $request_uri = rs_string($_SERVER['REQUEST_URI'] ?? '/', '/');
        $uri         = strtok($request_uri, '?');

        // strtok devuelve false solo con input vacío (URI ausente en CLI):
        // caemos a '/' y la normalización de Home de abajo cubre el resto.
        $current_path = rtrim($uri !== false ? $uri : '/', '/');

        // Normalizamos el path vacío para la Home si fuera necesario
        if ($current_path === '') $current_path = '/';

        foreach ($this->routes as $path => $callback) {
            $params = $this->match_route($path, $current_path);
            if ($params === null) {
                continue;
            }

            // Los placeholders llegan como strings (capturas de regex), pero
            // los handlers los declaran con su tipo natural (int $paged).
            // Con strict_types, la coerción silenciosa ya no ocurre: el
            // router convierte en el límite — segmento numérico => int/float.
            $params = array_map(
                static fn (string $value): int|float|string => match (true) {
                    preg_match('/^-?\d+$/', $value) === 1 => (int) $value,
                    is_numeric($value) => (float) $value,
                    default => $value,
                },
                $params
            );

            // Ruta encontrada: WP marcó 404 (no existe en la DB) porque
            // interceptamos después de la resolución de la query. Servimos
            // contenido real => forzamos 200 y limpiamos el flag is_404
            // (título "Page not found" y meta robots noindex dependen de él).
            $this->serve_as_found();

            // Callable sobre instancia ya configurada (FormController)
            if ($callback instanceof Closure) {
                $callback(...array_values($params));
                $this->terminate(); // Detenemos WordPress después de renderizar nuestra ruta virtual
            }

            // Par [ControllerClass|instancia, method]. El stack solo
            // registra callables en esta forma (los Closures se consumieron
            // arriba); el @var local lo declara para el análisis estático.
            /** @var array{0: class-string|object, 1: string} $callback */
            $controller = is_object($callback[0]) ? $callback[0] : new $callback[0]();
            $method = $callback[1];

            $controller->$method(...array_values($params));
            $this->terminate(); // Detenemos WordPress después de renderizar nuestra ruta virtual
        }
    }

    /**
     * Coincidencia exacta o por patrón con placeholders {param}.
     *
     * @return array<string, string>|null null si no coincide; los
     *         segmentos capturados por nombre (p.ej. {paged} => '2').
     *         El dispatcher convierte los numéricos a int|float antes de
     *         invocar (ver dispatch_routes).
     */
    private function match_route(string $path, string $current_path): ?array
    {
        if ($path === $current_path) {
            return [];
        }

        if (!str_contains($path, '{')) {
            return null;
        }

        $pattern = preg_replace(
            '~\{([a-zA-Z_][a-zA-Z0-9_]*)\}~',
            '(?P<$1>[^/]+)',
            $path,
        );

        if (preg_match('~^' . $pattern . '$~', $current_path, $m) !== 1) {
            return null;
        }

        // Solo los grupos nombrados (preg_match duplica índices numéricos).
        return array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
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
