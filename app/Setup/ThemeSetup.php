<?php

namespace RojasStack\Setup;

use Timber\Timber;
use RojasStack\Providers\AssetServiceProvider;

/**
 * ThemeSetup
 * 
 * Esta clase orquesta la configuración inicial del tema, registrando soportes,
 * menús y delegando la carga de assets y filtros.
 */
class ThemeSetup
{
    public function __construct()
    {
        // 1. Registrar Assets (Vite / Manifest)
        $assets = new AssetServiceProvider();
        $assets->register();

        // 2. Registrar Rutas Virtuales (Code-First Pages)
        $router = new \RojasStack\Providers\RouteServiceProvider();
        $router->register();

        // 3. Registrar Menús Programáticos
        $menus = new \RojasStack\Providers\MenuServiceProvider();
        $menus->register();

        // 4. Registrar SEO (JSON-LD)
        $seo = new \RojasStack\Providers\SeoServiceProvider();
        $seo->register();

        // 3. Registrar Controladores de Negocio
        $contact = new \RojasStack\Controllers\ContactController();
        $contact->register();

        // Hook para refrescar el formulario vía HTMX
        add_action('wp_ajax_get_contact_form', function() {
            \Timber\Timber::render('partials/contact-form.twig', \Timber\Timber::context());
            wp_die();
        });
        add_action('wp_ajax_nopriv_get_contact_form', function() {
            \Timber\Timber::render('partials/contact-form.twig', \Timber\Timber::context());
            wp_die();
        });

        // 4. Registrar Campos (Carbon Fields)
        new \RojasStack\Fields\ThemeOptions();
        new \RojasStack\Fields\PageFields();

        // 3. Registrar Hooks básicos
        $this->register_hooks();
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
