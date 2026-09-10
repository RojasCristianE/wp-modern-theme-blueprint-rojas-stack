<?php

declare(strict_types=1);

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
        $request_path = $this->resolve_request_path();

        // Generamos un objeto de menú "falso" pero estructurado como Timber espera
        $context['menu'] = (object) [
            'items' => array_map(
                function ($data, $index) use ($request_path) {
                    $url = rs_string($data['url'] ?? '');

                    return (object) [
                        'title'    => rs_string($data['title'] ?? ''),
                        'link'     => $url,
                        'current'  => $this->is_current_section($url, $request_path),
                        'children' => [],
                        'id'       => $index + 1000,
                    ];
                },
                $this->get_primary_menu_definition(),
                array_keys($this->get_primary_menu_definition()),
            ),
        ];

        return $context;
    }

    /**
     * Path de la petición actual según el objeto global $wp de WordPress
     * (sin query string — WP Core lo separa en ->request).
     */
    private function resolve_request_path(): string
    {
        $wp = $GLOBALS['wp'] ?? null;

        return $wp instanceof \WP ? rs_string($wp->request) : '';
    }

    /**
     * ¿Corresponde este item a la sección actual?
     *
     * Match por sección (prefijo de path): /blog ilumina también
     * /blog/page/2/ y /blog/mi-post/. Home ('/') solo matchea la raíz
     * exacta para no robarle el highlight a las secciones reales.
     */
    private function is_current_section(string $item_url, string $request_path): bool
    {
        $item_path = (string) (parse_url($item_url, PHP_URL_PATH) ?? '');

        $current = '/' . trim($request_path, '/');
        $item    = '/' . trim($item_path, '/');

        if ($item === '/') {
            return $current === '/';
        }

        return $current === $item || str_starts_with($current, rtrim($item, '/') . '/');
    }
}
