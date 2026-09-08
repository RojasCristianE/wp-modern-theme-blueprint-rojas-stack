<?php

namespace RojasStack\Controllers;

use Timber\Timber;

/**
 * SearchController
 *
 * Endpoint del fragmento de búsqueda en vivo (spec §9.3, contrato de
 * fragmentos): HTMX solicita admin-ajax?action=rs_live_search con el
 * término en 's' y recibe SOLO partials/search-results.twig para su
 * hx-swap dentro del modal. La búsqueda tradicional (/?s=) sigue
 * resolviendo por la jerarquía (search.php → search.twig).
 *
 * Es un provider del core (siempre activo): la búsqueda es funcionalidad
 * base del tema, no un módulo desactivable.
 */
class SearchController
{
    /**
     * Máximo de resultados por petición.
     */
    private int $per_page;

    public function __construct(int $per_page = 5)
    {
        $this->per_page = $per_page;
    }

    public function register(): void
    {
        // Sin login y con login (el modal está disponible para cualquier visitante)
        add_action('wp_ajax_rs_live_search', [$this, 'render_fragment']);
        add_action('wp_ajax_nopriv_rs_live_search', [$this, 'render_fragment']);
    }

    /**
     * Devuelve el fragmento de resultados para el hx-swap de HTMX.
     */
    public function render_fragment(): void
    {
        $search = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));

        if ($search === '') {
            // Término vacío: estado inicial del modal, sin consultar la DB.
            Timber::render('partials/search-results.twig', [
                'posts'        => [],
                'search_query' => '',
            ]);
            wp_die();
        }

        $context              = Timber::context();
        $context['posts']     = Timber::get_posts([
            's'              => $search,
            'posts_per_page' => $this->per_page,
        ]);
        $context['search_query'] = $search;

        Timber::render('partials/search-results.twig', $context);

        wp_die();
    }
}
