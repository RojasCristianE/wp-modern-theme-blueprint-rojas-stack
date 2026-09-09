<?php

declare(strict_types=1);

namespace RojasStack\Providers;

/**
 * ExampleCptServiceProvider
 *
 * Módulo 'example_cpt' (spec §5): registra un Custom Post Type de
 * ejemplo ("Podcast") con archive y single, y sirve de plantilla para
 * añadir CPTs propios a un proyecto derivado. Todo se parametriza desde
 * app/Config/features.php; viene desactivado por defecto.
 *
 * El archive (/{slug}/) y el single usan la jerarquía nativa de WP:
 * archive-podcast.twig y single-podcast.twig via Timber.
 */
class ExampleCptServiceProvider
{
    /**
     * Config del módulo 'example_cpt' (app/Config/features.php).
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
        add_action('init', [$this, 'register_post_type']);
    }

    /**
     * Registra el CPT con el slug/labels de features.php.
     */
    public function register_post_type(): void
    {
        /** @var non-empty-lowercase-string $slug */
        $slug     = (string) ($this->config['slug'] ?? 'podcast');
        $singular = (string) ($this->config['singular'] ?? 'Podcast');
        $plural   = (string) ($this->config['plural'] ?? 'Podcasts');

        register_post_type($slug, [
            'labels' => [
                'name'          => $plural,
                'singular_name' => $singular,
                'add_new_item'  => sprintf(__('Añadir %s', 'rojas-stack'), $singular),
                'edit_item'     => sprintf(__('Editar %s', 'rojas-stack'), $singular),
                'search_items'  => sprintf(__('Buscar %s', 'rojas-stack'), $plural),
                'not_found'     => sprintf(__('No hay %s todavía.', 'rojas-stack'), strtolower($plural)),
            ],
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite'      => ['slug' => $slug],
            'menu_icon'    => 'dashicons-microphone',
        ]);

        // Flush una sola vez para que el archive (/{slug}/) resuelva sin
        // tener que visitar Ajustes → Enlaces permanentes.
        $flush_flag = "rs_{$slug}_cpt_flushed";
        if (!get_option($flush_flag)) {
            flush_rewrite_rules(false);
            update_option($flush_flag, true);
        }
    }
}
