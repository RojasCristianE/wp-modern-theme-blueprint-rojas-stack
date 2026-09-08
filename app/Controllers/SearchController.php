<?php

declare(strict_types=1);

namespace RojasStack\Controllers;

use RojasStack\Contracts\SearchEngineInterface;
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
 * Fuente de datos: SearchEngineInterface — Meilisearch cuando está
 * configurado y disponible (typo-tolerance fuzzy); fallback nativo DB
 * (Timber::get_posts con 's') cuando falta config o el motor no
 * responde. Sin hits del motor NO hay fallback: el motor manda.
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

    /**
     * Motor de búsqueda inyectado; null = solo búsqueda nativa DB.
     */
    private ?SearchEngineInterface $engine;

    public function __construct(int $per_page = 6, ?SearchEngineInterface $engine = null)
    {
        $this->per_page = $per_page;
        $this->engine   = $engine;
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
            // Término vacío: estado inicial del modal, sin consultar nada.
            Timber::render('partials/search-results.twig', [
                'posts'        => [],
                'search_query' => '',
            ]);
            wp_die();
        }

        $context                 = Timber::context();
        $context['search_query'] = $search;
        $context['posts']        = $this->posts($search);

        Timber::render('partials/search-results.twig', $context);

        wp_die();
    }

    /**
     * Resultados: engine-first (fuzzy), fallback DB si el motor no
     * está disponible. El motor devuelve IDs en orden de relevancia;
     * para el fragmento SIEMPRE rehidratamos Timber posts (una sola
     * plantilla) y conservamos el orden con orderby=post__in.
     *
     * @return mixed Colección/iterable de posts de Timber para la vista.
     */
    private function posts(string $search): mixed
    {
        if ($this->engine !== null && $this->engine->is_available()) {
            $hits = $this->engine->search($search, $this->per_page);
            $ids  = array_values(array_filter(
                array_map(static fn ($hit): int => (int) ($hit['id'] ?? 0), $hits),
            ));

            if ($ids !== []) {
                return Timber::get_posts([
                    'post_type'           => 'any',
                    'post__in'            => $ids,
                    'orderby'             => 'post__in',
                    'posts_per_page'      => count($ids),
                    'ignore_sticky_posts' => true,
                    'no_found_rows'       => true,
                ]);
            }

            return [];
        }

        // Fallback nativo: búsqueda DB de WordPress/Timber (spec §9.3).
        return Timber::get_posts([
            's'              => $search,
            'posts_per_page' => $this->per_page,
        ]);
    }
}
