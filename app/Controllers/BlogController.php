<?php

namespace RojasStack\Controllers;

use Timber\Timber;

/**
 * BlogController
 * 
 * Gestiona las funcionalidades del blog (módulo 'blog' de features.php).
 */
class BlogController
{
    /**
     * Config del módulo 'blog' (app/Config/features.php).
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

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
        
        // Obtenemos los posts para el feed del blog ('paged' alimenta
        // components/pagination.twig — página 1 cuando el query no define una).
        $context['posts'] = Timber::get_posts([
            'posts_per_page' => (int) ($this->config['posts_per_page'] ?? 12),
            'paged'          => max(1, (int) get_query_var('paged')),
        ]);
        
        Timber::render('blog.twig', $context);
    }
}
