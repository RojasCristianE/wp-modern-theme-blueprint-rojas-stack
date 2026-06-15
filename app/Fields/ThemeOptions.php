<?php

namespace RojasStack\Fields;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * ThemeOptions
 * 
 * Define la página de opciones globales del tema en el panel de administración.
 */
class ThemeOptions
{
    public function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'register_options']);
    }

    public function register_options(): void
    {
        Container::make('theme_options', __('Opciones del Tema', 'rojas-stack'))
            ->set_icon('dashicons-admin-generic')
            ->add_fields([
                Field::make('text', 'rs_company_name', __('Nombre de la Empresa', 'rojas-stack'))
                    ->set_help_text(__('Aparecerá en el footer y en el Schema SEO.', 'rojas-stack')),
                
                Field::make('image', 'rs_logo', __('Logo Alternativo', 'rojas-stack'))
                    ->set_value_type('url'),

                Field::make('complex', 'rs_social_links', __('Redes Sociales', 'rojas-stack'))
                    ->add_fields([
                        Field::make('text', 'platform', __('Plataforma', 'rojas-stack')),
                        Field::make('text', 'url', __('Enlace', 'rojas-stack')),
                    ])
            ]);
    }
}
