<?php

namespace RojasStack\Controllers;

use Timber\Timber;

/**
 * ArchiveController
 *
 * Endpoint del fragmento de paginación para archives de contenido
 * (categorías, etiquetas y home del blog) — contrato de fragmentos
 * del stack: HTMX solicita admin-ajax?action=rs_archive_page y
 * recibe SOLO partials/archive-grid.twig (grid + paginación) para
 * su hx-swap. La visita directa a /category/slug/ sigue resolviendo
 * por la jerarquía (archive.twig) y renderiza la página completa.
 *
 * Es un provider del core (siempre activo): la paginación es
 * funcionalidad base del tema, no un módulo desactivable.
 */
class ArchiveController
{
    /**
     * Posts por página del fragmento (mismo valor que el blog).
     */
    private int $per_page;

    public function __construct(int $per_page = 6)
    {
        $this->per_page = max(1, $per_page);
    }

    public function register(): void
    {
        // El grid es contenido público: sin login y con login.
        add_action('wp_ajax_rs_archive_page', [$this, 'render_fragment']);
        add_action('wp_ajax_nopriv_rs_archive_page', [$this, 'render_fragment']);
    }

    /**
     * Devuelve el fragmento de grid + paginación para el hx-swap.
     *
     * Parámetros GET:
     * - taxonomy: taxonomy pública del post type 'post' (category|post_tag).
     * - term:     slug del término (opcional — sin término = home del blog).
     * - paged:    número de página (>= 1).
     */
    public function render_fragment(): void
    {
        $taxonomy = sanitize_key(wp_unslash($_GET['taxonomy'] ?? 'category'));
        if (!\in_array($taxonomy, get_object_taxonomies('post'), true)) {
            $taxonomy = 'category';
        }

        $term  = sanitize_title(wp_unslash($_GET['term'] ?? ''));
        $paged = max(1, (int) ($_GET['paged'] ?? 1));

        $args = [
            'post_type'           => 'post',
            'posts_per_page'      => $this->per_page,
            'paged'               => $paged,
            'ignore_sticky_posts' => true,
        ];

        if ($term !== '') {
            $args['tax_query'] = [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $term,
                ],
            ];
        }

        $context                = Timber::context();
        $context['posts']       = Timber::get_posts($args);
        $context['htmx_enabled'] = true;

        // El include con `only` no ve `site`: la URL del endpoint y la
        // base bonita del archive (para hx-push-url) llegan como
        // parámetros absolutos.
        $term_link = $term !== '' ? get_term_link($term, $taxonomy) : home_url('/');

        // Base bonita auto-descriptiva: la página que renderizó la
        // paginación la manda en el request (p.ej. /blog/ para la ruta
        // virtual) y el endpoint la hace eco; sin base válida, se deriva
        // del término (o home). El prefijo home_url() evita bases ajenas.
        $base = esc_url_raw(wp_unslash($_GET['base'] ?? ''));
        if ($base === '' || !str_starts_with($base, home_url())) {
            $base = is_wp_error($term_link) ? home_url('/') : $term_link;
        }

        $context['htmx_endpoint']    = admin_url('admin-ajax.php');
        $context['archive_taxonomy'] = $taxonomy;
        $context['archive_term']     = $term;
        $context['htmx_base']        = $base;

        Timber::render('partials/archive-grid.twig', $context);

        wp_die();
    }
}
