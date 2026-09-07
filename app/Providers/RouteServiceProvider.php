<?php

namespace RojasStack\Providers;

use Closure;

/**
 * RouteServiceProvider
 * 
 * Router genérico para rutas virtuales que no requieren una página creada
 * en la base de datos de WordPress. No conoce módulos: ThemeSetup registra
 * las rutas de cada feature habilitado vía add_route() (spec §5).
 * Permite definir la estructura del sitio 100% desde el código.
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
     * @param array{0: class-string, 1: string}|callable $callback
     *        [ControllerClass, method] o un callable sobre una instancia
     *        ya configurada (p.ej. FormController por formulario).
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
                // Callable sobre instancia ya configurada (FormController)
                if ($callback instanceof Closure) {
                    $callback();
                    exit; // Detenemos WordPress después de renderizar nuestra ruta virtual
                }

                // Par [ControllerClass, method] — instancia con estado o clase pura
                $controller = is_object($callback[0]) ? $callback[0] : new $callback[0]();
                $method = $callback[1];

                $controller->$method();
                exit; // Detenemos WordPress después de renderizar nuestra ruta virtual
            }
        }
    }
}
