<?php

declare(strict_types=1);

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
     * Renderiza la página del blog (Ruta Virtual).
     *
     * @param int $paged Página explícita capturada por el router
     *                   (/blog/page/{paged}); 0 = usar el query var.
     */
    public function render_page(int $paged = 0): void
    {
        $context = Timber::context();
        $context['title'] = __('Blog', 'rojas-stack');

        // Página explícita de la ruta virtual; si no llega, el query var
        // ('paged' alimenta components/pagination.twig).
        $page = max(1, $paged ?: rs_int(get_query_var('paged'), 0));

        $context['posts'] = Timber::get_posts([
            'posts_per_page' => rs_int($this->config['posts_per_page'] ?? 6, 6),
            'paged'          => $page,
        ]);

        // Contrato de partials/archive-grid.twig (paginación HTMX): sin
        // término (home del blog) y base bonita para hx-push-url.
        $context['archive_term'] = '';
        $context['htmx_base']    = home_url('/blog/');

        // partials/archive-shell.twig: cards del skeleton = tamaño de página.
        $context['posts_per_page'] = rs_int($this->config['posts_per_page'] ?? 6, 6);

        Timber::render('blog.twig', $context);
    }
}
