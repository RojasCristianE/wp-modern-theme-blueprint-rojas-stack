<?php

namespace RojasStack\Providers;

/**
 * MenuServiceProvider
 * 
 * Gestiona los menús de navegación de forma programática.
 * Version 2.0: Inyección ultra-compatible para Timber 2.0.
 */
class MenuServiceProvider
{
    /**
     * @return array<int, array<string, mixed>> Items del menú ('title', 'url')
     */
    public function get_primary_menu_definition(): array
    {
        return [
            ['title' => __('Inicio', 'rojas-stack'), 'url' => home_url('/')],
            ['title' => __('Blog', 'rojas-stack'), 'url' => home_url('/blog')],
            ['title' => __('Contacto', 'rojas-stack'), 'url' => home_url('/contacto')],
        ];
    }

    public function register(): void
    {
        // En lugar de filtrar WP Core (que causa TypeErrors en Timber),
        // inyectamos los items directamente en el contexto antes de que Timber los procese.
        add_filter('timber/context', [$this, 'inject_menu_to_context'], 20);
    }

    /**
     * @param array<string, mixed> $context Contexto base de Timber
     *
     * @return array<string, mixed>
     */
    public function inject_menu_to_context(array $context): array
    {
        // Generamos un objeto de menú "falso" pero estructurado como Timber espera
        $context['menu'] = (object) [
            'items' => array_map(function($data, $index) {
                return (object) [
                    'title'    => $data['title'],
                    'link'     => $data['url'],
                    'current'  => (trailingslashit($data['url']) === trailingslashit(home_url(add_query_arg([], $GLOBALS['wp']->request)))),
                    'children' => [],
                    'id'       => $index + 1000
                ];
            }, $this->get_primary_menu_definition(), array_keys($this->get_primary_menu_definition()))
        ];

        return $context;
    }
}
