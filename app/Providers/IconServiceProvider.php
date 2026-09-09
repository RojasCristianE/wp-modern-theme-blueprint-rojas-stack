<?php

/**
 * IconServiceProvider
 *
 * Expone IconService a las plantillas Twig como función global `rs_icon()`
 * (búsqueda: 'rs-icons-twig').
 *
 * Uso en Twig:
 *   {{ rs_icon('magnifying-glass') }}             → set primario (heroicons outline)
 *   {{ rs_icon('heroicons-solid:check-circle') }} → set explícito
 *   {{ rs_icon('arrow-right', { class: 'w-8 h-8' }) }}
 */

declare(strict_types=1);

namespace RojasStack\Providers;

use RojasStack\Services\IconService;
use Timber\Twig;

final class IconServiceProvider
{
    public function register(): void
    {
        add_filter('timber/twig', [$this, 'register_icon_function']);
    }

    /**
     * Añade rs_icon() al entorno Twig.
     *
     * @param \Twig\Environment $twig
     *
     * @return \Twig\Environment
     */
    public function register_icon_function($twig)
    {
        $twig->addFunction(new \Twig\TwigFunction('rs_icon', [$this, 'render_icon']));

        return $twig;
    }

    /**
     * Firma estable para Twig (los callbacks con variadics no serializan
     * bien en todos los entornos). Los atributos no-escalar los filtra
     * IconService::render() — aquí solo se normaliza la forma.
     *
     * @param mixed                     $name  Nombre 'name' o 'set:name'.
     * @param array<string, mixed>|null $attrs Atributos HTML extra.
     */
    public function render_icon($name, ?array $attrs = null): string
    {
        $name = is_string($name) ? $name : '';

        /** @var array<string, mixed> $safe_attrs */
        $safe_attrs = $attrs ?? [];

        return IconService::render($name, $safe_attrs);
    }
}
