<?php

/**
 * ImageServiceProvider
 *
 * Expone ImageService a las plantillas Twig como función global
 * `rs_image()` (pipeline único de imágenes, análogo a rs_icon()).
 *
 * Uso en Twig:
 *   {{ rs_image(post.thumbnail, 'card', { class: 'w-full h-48 object-cover' }) }}
 *   {{ rs_image(post.thumbnail, 'hero', { alt: 'Texto alternativo' }) }}
 */

declare(strict_types=1);

namespace RojasStack\Providers;

use RojasStack\Services\ImageService;

final class ImageServiceProvider
{
    public function register(): void
    {
        add_filter('timber/twig', [$this, 'register_image_function']);
    }

    /**
     * Añade rs_image() al entorno Twig.
     *
     * @param \Twig\Environment $twig
     *
     * @return \Twig\Environment
     */
    public function register_image_function($twig)
    {
        $twig->addFunction(new \Twig\TwigFunction('rs_image', [$this, 'render_image']));

        return $twig;
    }

    /**
     * Firma estable para Twig (sin variadics).
     *
     * @param mixed                     $attachment Timber\Image (o null)
     * @param string|null               $slot       Clave del catálogo
     * @param array<string, mixed>|null $attrs      Atributos extra
     */
    public function render_image($attachment, ?string $slot = 'card', ?array $attrs = null): string
    {
        /** @var array<string, mixed> $safe_attrs */
        $safe_attrs = $attrs ?? [];

        return ImageService::render($attachment, is_string($slot) ? $slot : 'card', $safe_attrs);
    }
}
