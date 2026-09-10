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

// Posts por página en feeds paginados (blog y archives). Una sola
// fuente de verdad para TODAS las superficies: variable de entorno
// POSTS_PER_PAGE (ver .env.example), 6 por defecto.
$posts_per_page = max(1, rs_int($_ENV['POSTS_PER_PAGE'] ?? $_SERVER['POSTS_PER_PAGE'] ?? 6, 6));

return [
    // Override por instancia si un módulo lo necesita; si no, hereda
    // el valor global de arriba.
    'posts_per_page' => $posts_per_page,

    // Motor de búsqueda (SearchEngineInterface). Meilisearch se activa
    // SOLO si hay credenciales en .env (MEILI_HOST + MEILI_API_KEY);
    // sin ellas (o con MEILI_ENABLED=false) el SearchController usa la
    // búsqueda nativa DB. Con el motor activo y caído, el fallback es
    // automático e invisible. Nota: el índice es EVENTUAL — edits recientes
    // pueden tardar en aparecer hasta la próxima sincronización.
    'search' => [
        'enabled' => ($_ENV['MEILI_HOST'] ?? $_SERVER['MEILI_HOST'] ?? '') !== ''
            && ($_ENV['MEILI_ENABLED'] ?? $_SERVER['MEILI_ENABLED'] ?? 'true') !== 'false',
    ],

    // Media pipeline (spec §12.1): webp en todas las variantes, tamaños
    // default recortados y proxy de variantes on-demand (imgproxy).
    // Proxy: activo SOLO si IMGPROXY_ROUTE está definido (.env) — sin
    // ruta configurada, las URLs caen al resize nativo de Timber (comport
    // previo). Warm-up: cola Action Scheduler (WARMUP_ENABLED=true).
    'media' => [
        'enabled' => true,
        'proxy' => [
            'enabled' => ($_ENV['IMGPROXY_ROUTE'] ?? $_SERVER['IMGPROXY_ROUTE'] ?? '') !== '',
            'route'   => rs_string($_ENV['IMGPROXY_ROUTE'] ?? $_SERVER['IMGPROXY_ROUTE'] ?? ''),
        ],
        'warmup' => [
            'enabled' => ($_ENV['WARMUP_ENABLED'] ?? $_SERVER['WARMUP_ENABLED'] ?? 'false') === 'true',
            'widths'  => [480, 800, 1280],
        ],
    ],

    'blog' => [
        'enabled' => true,
        'posts_per_page' => $posts_per_page,
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

        // Cada entrada es una instancia del FormController genérico
        // (spec paso 7): ruta virtual, templates, campos/reglas Rakit,
        // destinatario y textos de éxito, todo por formulario.
        'forms' => [
            'contact' => [
                'route' => '/contacto',
                'title' => 'Contáctanos',
                'page_template' => 'contact.twig',
                'form_template' => 'partials/contact-form.twig',
                'success_template' => 'partials/form-success.twig',
                // 'admin_email' = get_option('admin_email'); o un email explícito.
                'recipient' => 'admin_email',
                // Token ':name' se reemplaza por el valor del campo name.
                'subject' => 'Nuevo mensaje de: :name',
                'fields' => [
                    'name' => 'required|min:3',
                    'email' => 'required|email',
                    'message' => 'required|min:10',
                ],
                'success' => [
                    'title' => '¡Mensaje Enviado!',
                    'message' => 'Tu mensaje ha sido capturado por Mailpit con éxito.',
                    // Selector HTMX del wrapper que hx-swap reemplaza.
                    'target' => '#contact-form-wrapper',
                ],
            ],
        ],
    ],

    'seo' => [
        'enabled' => true,
        // null = fallback: Carbon Fields rs_company_name → bloginfo('name')
        'organization_name' => null,
    ],

    // Ruta /__components con el catálogo de componentes (spec §8.3).
    // En producción queda deshabilitada salvo override manual.
    'design_system' => [
        'enabled' => ($_ENV['WP_ENV'] ?? '') === 'development',
    ],
];
