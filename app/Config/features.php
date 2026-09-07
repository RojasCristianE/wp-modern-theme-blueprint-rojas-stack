<?php

declare(strict_types=1);

/**
 * Configuración central de módulos del Rojas Stack (spec §5).
 *
 * Única fuente de verdad para activar/desactivar funcionalidades y
 * parametrizarlas. ThemeSetup lee este archivo y solo registra los
 * módulos habilitados; `core` (bootstrap, providers base, helpers,
 * Twig) es siempre activo y no se configura aquí.
 *
 * Estados por defecto según spec §5:
 *   blog → true | pages → true | forms → true | seo → true
 *   example_cpt → false | design_system → solo en WP_ENV=development
 */

return [
    'blog' => [
        'enabled' => true,
        'posts_per_page' => 12,
        'show_author' => true,
        'show_categories' => true,
    ],

    'pages' => [
        'enabled' => true,
    ],

    // Spec paso 6: CPT de ejemplo pendiente de implementación;
    // la llave queda lista para activarse por proyecto.
    'example_cpt' => [
        'enabled' => false,
        'slug' => 'podcast',
        'singular' => 'Podcast',
        'plural' => 'Podcasts',
    ],

    'forms' => [
        'enabled' => true,
        // 'admin_email' = get_option('admin_email'); o un email explícito.
        'contact_recipient' => 'admin_email',
    ],

    'seo' => [
        'enabled' => true,
        // null = fallback: Carbon Fields rs_company_name → bloginfo('name')
        'organization_name' => null,
    ],

    // Spec pasos 4 y 10: ruta /__components pendiente de implementación.
    'design_system' => [
        'enabled' => ($_ENV['WP_ENV'] ?? '') === 'development',
    ],
];
