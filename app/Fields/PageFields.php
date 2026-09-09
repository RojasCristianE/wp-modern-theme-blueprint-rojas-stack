<?php

declare(strict_types=1);

namespace RojasStack\Fields;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * PageFields
 * 
 * Define campos específicos para páginas, como la sección Hero de la Home.
 */
class PageFields
{
    public function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'register_page_fields']);
    }

    public function register_page_fields(): void
    {
        Container::make('post_meta', __('Configuración de Hero', 'rojas-stack'))
            ->where('post_type', '=', 'page')
            ->where('post_id', '=', get_option('page_on_front')) // Solo en la Front Page
            ->add_fields([
                Field::make('text', 'hero_title', __('Título del Hero', 'rojas-stack'))
                    ->set_help_text(__('Sobrescribe el título principal en la Home.', 'rojas-stack')),
                
                Field::make('textarea', 'hero_subtitle', __('Subtítulo del Hero', 'rojas-stack')),
                
                Field::make('text', 'hero_cta_text', __('Texto del Botón', 'rojas-stack')),
                Field::make('text', 'hero_cta_url', __('Enlace del Botón', 'rojas-stack')),
            ]);
    }
}
