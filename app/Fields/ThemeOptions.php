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
        /** @var \Carbon_Fields\Container\Theme_Options_Container $container */
        $container = Container::make('theme_options', __('Opciones del Tema', 'rojas-stack'));

        /** @var \Carbon_Fields\Field\Image_Field $logo */
        $logo = Field::make('image', 'rs_logo', __('Logo Alternativo', 'rojas-stack'));

        /** @var \Carbon_Fields\Field\Complex_Field $social_links */
        $social_links = Field::make('complex', 'rs_social_links', __('Redes Sociales', 'rojas-stack'));

        $container->add_fields([
            Field::make('text', 'rs_company_name', __('Nombre de la Empresa', 'rojas-stack'))
                ->set_help_text(__('Aparecerá en el footer y en el Schema SEO.', 'rojas-stack')),

            $logo->set_value_type('url'),

            $social_links->add_fields([
                Field::make('text', 'platform', __('Plataforma', 'rojas-stack')),
                Field::make('text', 'url', __('Enlace', 'rojas-stack')),
            ]),
        ]);
    }
}
