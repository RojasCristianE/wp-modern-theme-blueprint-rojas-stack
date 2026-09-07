<?php

namespace RojasStack\Controllers;

use Timber\Timber;

/**
 * DesignSystemController
 * 
 * Sirve la página /__components con el catálogo de componentes Twig
 * (módulo 'design_system' de features.php, solo en desarrollo).
 */
class DesignSystemController
{
    public function register(): void
    {
        // El enrutado lo maneja RouteServiceProvider vía add_route();
        // no se necesitan hooks propios por ahora.
    }

    /**
     * Renderiza el catálogo de componentes (Ruta Virtual).
     */
    public function render_page(): void
    {
        $context = Timber::context();
        $context['title'] = __('Design System', 'rojas-stack');

        Timber::render('design-system.twig', $context);
    }
}
