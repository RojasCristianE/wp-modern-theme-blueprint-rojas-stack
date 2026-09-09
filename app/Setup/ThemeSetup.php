<?php

declare(strict_types=1);

namespace RojasStack\Setup;

use RojasStack\Controllers\ArchiveController;
use RojasStack\Controllers\BlogController;
use RojasStack\Controllers\DesignSystemController;
use RojasStack\Controllers\FormController;
use RojasStack\Controllers\SearchController;
use RojasStack\Fields\PageFields;
use RojasStack\Fields\ThemeOptions;
use RojasStack\Providers\AssetServiceProvider;
use RojasStack\Providers\ExampleCptServiceProvider;
use RojasStack\Providers\IconServiceProvider;
use RojasStack\Providers\MenuServiceProvider;
use RojasStack\Providers\RouteServiceProvider;
use RojasStack\Providers\SeoServiceProvider;
use RojasStack\Services\MeilisearchClient;
use RojasStack\Services\MeilisearchIndexer;
use WP_Query;

/**
 * ThemeSetup
 *
 * Orquestador del tema: registra el núcleo (core: assets, menús, router,
 * hooks y campos globales) y los módulos activables declarados en
 * app/Config/features.php (spec §5). Un módulo deshabilitado no registra
 * rutas, hooks ni campos.
 */
class ThemeSetup
{
    /**
     * Posts por página global (POSTS_PER_PAGE en .env → features.php).
     * Una sola fuente de verdad para TODAS las superficies (spec §12.1).
     */
    private int $posts_per_page;

    public function __construct()
    {
        $loaded_features = require get_template_directory() . '/app/Config/features.php';

        /** @var array<string|int, mixed> $features Contrato de features.php */
        $features = rs_array($loaded_features);
        $this->posts_per_page = rs_int($features['posts_per_page'] ?? 6, 6);

        // --- Core (siempre activo) ---
        // Iconos: sets sincronizados a resources/icons/ vía bin/sync-icons.sh
        // (spec §12.1, entrada 8). Función Twig global rs_icon().
        (new IconServiceProvider())->register();

        (new AssetServiceProvider())->register();

        $router = new RouteServiceProvider();
        $router->register();

        (new MenuServiceProvider())->register();

        // Búsqueda en vivo (core): endpoint del fragmento para HTMX (spec §9.3).
        // Mismo posts_per_page global que blog y archives (POSTS_PER_PAGE).
        // Con Meilisearch configurado (.env: MEILI_HOST + MEILI_API_KEY),
        // el controller busca engine-first (fuzzy) con fallback DB automático.
        $search_engine = null;

        if (self::enabled($features, 'search')) {
            $search_engine = new MeilisearchClient();
            (new MeilisearchIndexer())->register();
        }

        (new SearchController($this->posts_per_page, $search_engine))->register();

        // Paginación de archives (core): fragmento grid+paginación para HTMX.
        // Mismo posts_per_page que el blog: una sola fuente de verdad
        // (POSTS_PER_PAGE en .env, top-level de features.php).
        (new ArchiveController($this->posts_per_page))->register();

        $this->register_hooks();
        $this->register_core_fields();

        // --- Módulos activables ---
        if (self::enabled($features, 'pages')) {
            $this->register_pages_fields();
        }

        if (self::enabled($features, 'blog')) {
            $router->add_route('/blog', [BlogController::class, 'render_page']);
            // Paginación de la ruta virtual: el placeholder captura el
            // número y el router lo pasa como argumento al controller.
            $router->add_route('/blog/page/{paged}', [BlogController::class, 'render_page']);
            /** @var array<string, mixed> $blog_config */
            $blog_config = rs_array($features['blog'] ?? []);
            (new BlogController($blog_config))->register();
        }

        if (self::enabled($features, 'forms')) {
            // Cada formulario definido en features.php es una instancia
            // del FormController genérico (spec paso 7).
            $forms_module = rs_array($features['forms'] ?? []);

            /** @var array<string, array<string, mixed>> $forms */
            $forms = rs_array($forms_module['forms'] ?? []);

            foreach ($forms as $key => $form_config) {
                $form = new FormController($key, $form_config);
                $form->register();

                if (isset($form_config['route'])) {
                    $router->add_route(rs_string($form_config['route']), [$form, 'render_page']);
                }
            }
        }

        if (self::enabled($features, 'seo')) {
            /** @var array<string, mixed> $seo_config */
            $seo_config = rs_array($features['seo'] ?? []);
            (new SeoServiceProvider($seo_config))->register();
        }

        if (self::enabled($features, 'design_system')) {
            $router->add_route('/__components', [DesignSystemController::class, 'render_page']);
        }

        if (self::enabled($features, 'example_cpt')) {
            /** @var array<string, mixed> $cpt_config */
            $cpt_config = rs_array($features['example_cpt'] ?? []);
            (new ExampleCptServiceProvider($cpt_config))->register();
        }
    }

    /**
     * Un módulo está habilitado si su llave 'enabled' es truthy.
     *
     * @param array<string|int, mixed> $features
     */
    private static function enabled(array $features, string $module): bool
    {
        $config = $features[$module] ?? [];

        return (bool) (\is_array($config) ? ($config['enabled'] ?? false) : false);
    }

    /**
     * Campos globales del tema (branding, opciones).
     */
    private function register_core_fields(): void
    {
        new ThemeOptions();
    }

    /**
     * Campos del módulo 'pages' (hero de la portada, etc.).
     */
    private function register_pages_fields(): void
    {
        new PageFields();
    }

    /**
     * Registra los hooks de acción principales de WordPress.
     */
    protected function register_hooks(): void
    {
        add_action('after_setup_theme', [$this, 'add_theme_supports']);
        add_action('after_setup_theme', [$this, 'register_menus']);

        // Una sola fuente de verdad: los archives nativos (categoría,
        // etiqueta, home) también respetan POSTS_PER_PAGE — los
        // controllers del tema ya lo hacen por constructor.
        add_action('pre_get_posts', [$this, 'enforce_posts_per_page']);

        // Filtros de Timber
        add_filter('timber/context', [$this, 'add_to_context']);
    }

    /**
     * Define las capacidades soportadas por el tema.
     */
    public function add_theme_supports(): void
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
        add_theme_support('menus');
    }

    /**
     * Registra las ubicaciones de los menús de navegación.
     */
    public function register_menus(): void
    {
        register_nav_menus([
            'primary_menu' => __('Menú Principal', 'rojas-stack'),
            'footer_menu'  => __('Menú Footer', 'rojas-stack'),
        ]);
    }

    /**
     * Impone POSTS_PER_PAGE sobre la main query de WordPress: los
     * archives nativos (categoría, etiqueta, home) leerían la option
     * posts_per_page de la DB (default WP: 10), que el stack no
     * controla. El admin queda excluido (sus listas siguen en su
     * default) y solo se toca la main query — loops secundarios no.
     */
    public function enforce_posts_per_page(WP_Query $query): void
    {
        if (is_admin() || ! $query->is_main_query()) {
            return;
        }

        $query->set('posts_per_page', $this->posts_per_page);
    }

    /**
     * Inyecta las opciones globales del tema (Carbon Fields) en el contexto de Timber.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function add_to_context(array $context): array
    {
        // Inyectar opciones globales del tema (Carbon Fields)
        $context['options'] = [
            'company_name' => carbon_get_theme_option('rs_company_name'),
            'logo'         => carbon_get_theme_option('rs_logo'),
            'social'       => carbon_get_theme_option('rs_social_links'),
        ];
        
        return $context;
    }
}
