<?php

namespace RojasStack\Controllers;

use Timber\Timber;

/**
 * BlogController
 * 
 * Gestiona las funcionalidades del blog.
 */
class BlogController
{
    public function register(): void
    {
        // Aquí se podrían registrar hooks específicos del blog si fuera necesario
    }

    /**
     * Renderiza la página del blog (Ruta Virtual)
     */
    public function render_page(): void
    {
        $context = Timber::context();
        $context['title'] = __('Blog', 'rojas-stack');
        
        // Obtenemos los posts para el feed del blog
        $context['posts'] = Timber::get_posts();
        
        Timber::render('blog.twig', $context);
    }
}
