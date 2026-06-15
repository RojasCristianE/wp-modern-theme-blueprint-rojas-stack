<?php

namespace RojasStack\Providers;

use RojasStack\Controllers\ContactController;

/**
 * RouteServiceProvider
 * 
 * Gestiona rutas virtuales que no requieren una página creada en la base de datos de WordPress.
 * Permite definir la estructura del sitio 100% desde el código.
 */
class RouteServiceProvider
{
    /**
     * Mapa de rutas: 'slug' => ['ControllerClass', 'method']
     */
    private array $routes = [
        '/contacto' => [ContactController::class, 'render_page'],
    ];

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
                $controllerClass = $callback[0];
                $method = $callback[1];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    $controller->$method();
                    exit; // Detenemos WordPress después de renderizar nuestra ruta virtual
                }
            }
        }
    }
}
