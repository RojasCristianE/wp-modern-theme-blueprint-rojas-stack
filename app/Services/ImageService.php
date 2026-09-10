<?php

declare(strict_types=1);

namespace RojasStack\Services;

use RojasStack\Providers\MediaServiceProvider;
use Timber\ImageHelper;

/**
 * ImageService
 *
 * Pipeline único de imágenes del tema (análogo a IconService): una API,
 * dos motores.
 *
 *   - Con proxy (IMGPROXY_ROUTE en .env): srcset con buckets cuantizados
 *     servidos por imgproxy/Caddy — variantes on-demand con caché
 *     efímera (spec §12.1, entrada 13).
 *   - Sin proxy: resize nativo de Timber para el ancho base del slot y
 *     SIN srcset (el resize nativo genera la variante en primer plano:
 *     anunciar 3 candidatos bloquearía el primer render con 3 resizes).
 *
 * Los slots usan CSS object-cover para el encuadre: las variantes son
 * width-only y los atributos width/height llevan el ratio del slot para
 * CLS. Los slots LCP (hero/full) nunca llevan loading="lazy".
 */
final class ImageService
{
    /**
     * Catálogo de slots. base = ancho del variant default; ratio =
     * aspect del slot (para width/height y la altura del resize nativo);
     * lcp = imagen sobre el pliegue (sin lazy, fetchpriority=high).
     *
     * @var array<string, array{base: int, ratio: float, lcp: bool, sizes: string}>
     */
    private const SLOTS = [
        // Cards del blog/archive/search: 3:2, ancho de columna.
        'card'  => ['base' => 600,  'ratio' => 1.5,    'lcp' => false, 'sizes' => 'auto'],
        // Thumbnail cuadrado de resultados de búsqueda.
        'thumb' => ['base' => 96,   'ratio' => 1.0,    'lcp' => false, 'sizes' => 'auto'],
        // Featured de single/podcast: 16:9 full-width, LCP.
        'hero'  => ['base' => 1200, 'ratio' => 1.7778, 'lcp' => true,  'sizes' => '100vw'],
        // Original sin redimensionar (LCP).
        'full'  => ['base' => 0,    'ratio' => 0.0,    'lcp' => true,  'sizes' => '100vw'],
    ];

    /**
     * Renderiza el <img> completo para un attachment de Timber y un slot.
     *
     * @param mixed                $attachment Timber\Image (o null → '')
     * @param string               $slot       Clave del catálogo SLOTS
     * @param array<string, mixed> $attrs      Override/extra: alt, class,
     *                                         sizes (atributo completo).
     */
    public static function render(mixed $attachment, string $slot = 'card', array $attrs = []): string
    {
        $config = self::SLOTS[$slot] ?? null;
        $src    = self::attachment_src($attachment);

        if ($config === null || $src === '') {
            return '';
        }

        $base    = (int) $config['base'];
        $ratio   = (float) $config['ratio'];
        $is_lcp  = (bool) $config['lcp'];
        $classes = rs_string($attrs['class'] ?? '');
        $alt     = rs_string($attrs['alt'] ?? self::attachment_alt($attachment));
        $sizes   = rs_string($attrs['sizes'] ?? $config['sizes']);

        $parts = [
            'src'      => self::variant_url($src, $base),
            'alt'      => $alt,
            'class'    => $classes,
            'decoding' => 'async',
        ];

        // CLS: ratio del slot (original para 'full' — dims del attachment).
        if ($base > 0 && $ratio > 0) {
            $parts['width']  = (string) $base;
            $parts['height'] = (string) (int) round($base / $ratio);
        } elseif ($base === 0) {
            $width  = 0;
            $height = 0;

            if (is_object($attachment)) {
                // API de Timber: width()/height() son métodos (las
                // propiedades mágicas no responden a isset ni siempre
                // materializan).
                if (method_exists($attachment, 'width')) {
                    $width = rs_int($attachment->width(), 0);
                } else {
                    $width = rs_int($attachment->width ?? 0, 0);
                }

                if (method_exists($attachment, 'height')) {
                    $height = rs_int($attachment->height(), 0);
                } else {
                    $height = rs_int($attachment->height ?? 0, 0);
                }
            }

            if ($width > 0 && $height > 0) {
                $parts['width']  = (string) $width;
                $parts['height'] = (string) $height;
            }
        }

        // LCP: prioritario, sin lazy. Below-fold: lazy + sizes auto.
        if ($is_lcp) {
            $parts['fetchpriority'] = 'high';
        } else {
            $parts['loading'] = 'lazy';
            $parts['sizes']   = $sizes !== '' ? $sizes : 'auto';
        }

        // srcset solo con proxy (buckets on-demand). Ver docblock de clase.
        $srcset = self::srcset($src, $base);

        if ($srcset !== '') {
            $parts['srcset'] = $srcset;
        }

        return self::build_img($parts);
    }

    /**
     * URL de variante para un src y ancho: proxy cuantizado si hay
     * IMGPROXY_ROUTE; resize nativo de Timber como fallback.
     */
    public static function variant_url(string $src, int $width): string
    {
        if ($width <= 0) {
            return $src;
        }

        $route = rs_string($_ENV['IMGPROXY_ROUTE'] ?? $_SERVER['IMGPROXY_ROUTE'] ?? '');

        if ($route !== '') {
            return MediaServiceProvider::proxy_url($src, $width);
        }

        $ratio = self::ratio_for_width($width);

        return self::native_resize($src, $width, $ratio > 0 ? (int) round($width / $ratio) : 0);
    }

    /**
     * Fallback nativo (Timber). Método público y aislado a propósito:
     * los tests lo redefinen con Patchwork — el resize real escribe en
     * uploads/ y no es territorio de tests unitarios.
     */
    public static function native_resize(string $src, int $width, int $height): string
    {
        return (string) ImageHelper::resize($src, $width, $height);
    }

    /**
     * srcset de buckets >= base (máx 1920) — solo con proxy configurado.
     */
    private static function srcset(string $src, int $base): string
    {
        $route = rs_string($_ENV['IMGPROXY_ROUTE'] ?? $_SERVER['IMGPROXY_ROUTE'] ?? '');

        if ($route === '' || $base <= 0) {
            return '';
        }

        $candidates = [];

        foreach (MediaServiceProvider::BUCKETS as $bucket) {
            if ($bucket >= $base) {
                $candidates[] = MediaServiceProvider::proxy_url($src, $bucket) . ' ' . $bucket . 'w';
            }
        }

        return implode(', ', $candidates);
    }

    /**
     * Ratio del slot correspondiente a un ancho base (para la altura del
     * resize nativo). Los buckets intermedios heredan el ratio del slot
     * más cercano por debajo.
     */
    private static function ratio_for_width(int $width): float
    {
        $best = 0.0;

        foreach (self::SLOTS as $config) {
            $base = (int) $config['base'];

            if ($base > 0 && $base <= $width && $base > $best * 1000) {
                $best = (float) $config['ratio'];
            }
        }

        return $best;
    }

    /**
     * Extrae el src de un attachment Timber (objeto, array o string).
     *
     * API de Timber: Image::src() es MÉTODO — las propiedades mágicas
     * no responden a isset() (sin __isset) y no deben usarse como guard.
     */
    private static function attachment_src(mixed $attachment): string
    {
        if (is_string($attachment)) {
            return $attachment;
        }

        if (is_object($attachment) && method_exists($attachment, 'src')) {
            return rs_string($attachment->src());
        }

        if (is_object($attachment) && isset($attachment->src)
            && is_string($attachment->src)) {
            return $attachment->src;
        }

        return '';
    }

    /**
     * Alt del attachment (Timber: Image::alt() método) con fallback vacío.
     */
    private static function attachment_alt(mixed $attachment): string
    {
        if (is_object($attachment) && method_exists($attachment, 'alt')) {
            return rs_string($attachment->alt() ?? '');
        }

        if (is_object($attachment) && isset($attachment->alt)
            && is_string($attachment->alt)) {
            return $attachment->alt;
        }

        return '';
    }

    /**
     * Construye el <img> escapando cada atributo.
     *
     * @param array<string, string> $parts
     */
    private static function build_img(array $parts): string
    {
        $html = '<img';

        foreach ($parts as $name => $value) {
            if ($value === '') {
                continue;
            }

            $html .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        return $html . '>';
    }
}
