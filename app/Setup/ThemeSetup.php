<?php

namespace RojasStack\Setup;

use RojasStack\Controllers\BlogController;
use RojasStack\Controllers\ContactController;
use RojasStack\Controllers\DesignSystemController;
use RojasStack\Fields\PageFields;
use RojasStack\Fields\ThemeOptions;
use RojasStack\Providers\AssetServiceProvider;
use RojasStack\Providers\MenuServiceProvider;
use RojasStack\Providers\RouteServiceProvider;
use RojasStack\Providers\SeoServiceProvider;

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
    public function __construct()
    {
        $features = require get_template_directory() . '/app/Config/features.php';

        // --- Core (siempre activo) ---
        (new AssetServiceProvider())->register();

        $router = new RouteServiceProvider();
        $router->register();

        (new MenuServiceProvider())->register();

        $this->register_hooks();
        $this->register_core_fields();

        // --- Módulos activables ---
        if (self::enabled($features, 'pages')) {
            $this->register_pages_fields();
        }

        if (self::enabled($features, 'blog')) {
            $router->add_route('/blog', [BlogController::class, 'render_page']);
            (new BlogController($features['blog'] ?? []))->register();
        }

        if (self::enabled($features, 'forms')) {
            $router->add_route('/contacto', [ContactController::class, 'render_page']);
            (new ContactController($features['forms'] ?? []))->register();
        }

        if (self::enabled($features, 'seo')) {
            (new SeoServiceProvider($features['seo'] ?? []))->register();
        }

        if (self::enabled($features, 'design_system')) {
            $router->add_route('/__components', [DesignSystemController::class, 'render_page']);
        }
    }

    /**
     * Un módulo está habilitado si su llave 'enabled' es truthy.
     *
     * @param array<string, mixed> $features
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
