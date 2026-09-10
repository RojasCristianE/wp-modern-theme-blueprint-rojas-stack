<?php

declare(strict_types=1);

namespace RojasStack\Providers;

use ActionScheduler;

/**
 * MediaServiceProvider
 *
 * Pipeline de media (spec §12.1):
 *
 *  - WebP como formato de salida del editor de WP, calidad 82, y
 *    recorte de los tamaños default que el tema no consume.
 *  - Variantes on-demand: si hay proxy configurado (IMGPROXY_ROUTE en
 *    .env), rs_image()/image.twig construyen URLs del proxy
 *    (imgproxy detrás de Caddy en prod); sin proxy, el fallback es el
 *    resize nativo de Timber (genera y cachea en uploads/).
 *  - Warm-up opcional: al publicar (publish_post), encola con Action
 *    Scheduler la petición de los anchos principales para que la caché
 *    de disco del proxy y la CDN ya tengan las variantes cuando llegue
 *    el tráfico (WARMUP_ENABLED=true).
 */
final class MediaServiceProvider
{
    /**
     * Anchos cuantizados (buckets). El proxy solo materializa estos
     * anchos: evita miles de variantes microscópicas por DPR/viewport.
     *
     * @var list<int>
     */
    public const BUCKETS = [320, 480, 640, 800, 1024, 1280, 1600, 1920];

    /**
     * @param array<string, mixed> $config Entrada 'media' de features.php
     */
    public function __construct(
        private readonly array $config = [],
    ) {
    }

    public function register(): void
    {
        add_filter('image_editor_output_format', [$this, 'force_webp_output']);
        add_filter('jpeg_quality', fn (): int => 82);
        add_filter('wp_editor_set_quality', fn (): int => 82);

        // Tamaños default de WP que el tema no consume: fuera. El disco
        // guarda originales + solo los tamaños que el stack renderiza.
        add_filter('intermediate_image_sizes_advanced', [$this, 'trim_default_sizes']);
        add_filter('fallback_intermediate_image_sizes', fn (): array => []);

        if ($this->warmup_enabled()) {
            add_action('init', [$this, 'register_warmup_action']);
            add_action('publish_post', [$this, 'enqueue_warmup']);
        }
    }

    /**
     * ¿Proxy de variantes configurado? (IMGPROXY_ROUTE en .env).
     */
    public function proxy_enabled(): bool
    {
        /** @var array<string, mixed> $proxy */
        $proxy = rs_array($this->config['proxy'] ?? []);

        return (bool) ($proxy['enabled'] ?? false)
            && rs_string($proxy['route'] ?? '') !== '';
    }

    /**
     * ¿Warm-up con Action Scheduler activado? (WARMUP_ENABLED=true).
     */
    public function warmup_enabled(): bool
    {
        /** @var array<string, mixed> $warmup */
        $warmup = rs_array($this->config['warmup'] ?? []);

        return (bool) ($warmup['enabled'] ?? false);
    }

    /**
     * WebP como formato de salida del editor de WP (PNG mantiene alpha).
     *
     * @param array<string, string> $formats
     *
     * @return array<string, string>
     */
    public function force_webp_output(array $formats): array
    {
        $formats['image/jpeg'] = 'image/webp';
        $formats['image/png']  = 'image/webp';

        return $formats;
    }

    /**
     * Recorta los tamaños default de WP: el tema solo consume featured
     * images vía el pipeline propio (rs_image/image.twig).
     *
     * @param array<string, mixed> $sizes
     *
     * @return list<string>
     */
    public function trim_default_sizes(array $sizes): array
    {
        return ['medium', 'large'];
    }

    /**
     * URL de variante del proxy para un src y ancho pedidos.
     *
     * El ancho se cuantiza al bucket inmediato superior (BUCKETS): el
     * proxy solo materializa anchos de la lista y las capas de caché
     * (CDN edge → disco del proxy) los vuelven estáticos.
     *
     * @return string URL absoluta (ej. https://site.com/cdn-cgi/image/w:800/2026/09/foto.jpg)
     */
    public static function proxy_url(string $src, int $width): string
    {
        $base = rs_string($_ENV['IMGPROXY_ROUTE'] ?? $_SERVER['IMGPROXY_ROUTE'] ?? '');

        if ($base === '') {
            return $src;
        }

        $relative = self::uploads_relative_path($src);

        // URL externa (CDN propia, picsum en demos): el proxy local no
        // la sirve — devolver el src intacto.
        if ($relative === $src) {
            return $src;
        }

        return rtrim($base, '/') . '/w:' . self::bucket_for($width) . '/' . $relative;
    }

    /**
     * Bucket inmediato superior para el ancho pedido.
     */
    public static function bucket_for(int $width): int
    {
        foreach (self::BUCKETS as $bucket) {
            if ($bucket >= $width) {
                return $bucket;
            }
        }

        // Mayores que el bucket máximo: ancho pedido sin upsize.
        return $width;
    }

    /**
     * Path relativo a la raíz de WP (contrato del proxy:
     * /w:{bucket}/wp-content/uploads/Y/m/file.jpg — el LOCAL_FILESYSTEM_ROOT
     * de imgproxy apunta a la raíz del Core Fantasma, no a uploads/).
     * Devuelve el src intacto si no pertenece a uploads/.
     */
    public static function uploads_relative_path(string $src): string
    {
        $uploads = wp_upload_dir();
        $baseurl = rs_string($uploads['baseurl']);

        $uploads_marker = '/wp-content/uploads/';
        $marker_pos = strpos($src, $uploads_marker);

        if ($baseurl !== '' && $marker_pos !== false && str_starts_with($src, $baseurl . '/')) {
            // Del src absoluto conservamos wp-content/uploads/... (contrato
            // del proxy: path relativo a la raíz de WP).
            return substr($src, $marker_pos + 1);
        }

        return $src;
    }

    /**
     * Registra el callback del warm-up (Action Scheduler se carga con
     * composer; el runner corre vía WP-Cron sin admin UI).
     */
    public function register_warmup_action(): void
    {
        if (! class_exists(ActionScheduler::class)) {
            return;
        }

        add_action('rs_warmup_responsive_images', [$this, 'warmup_attachment']);
    }

    /**
     * Hook publish_post: encola el warm-up de la featured image.
     */
    public function enqueue_warmup(int $post_id): void
    {
        if (! class_exists(ActionScheduler::class) || ! function_exists('as_enqueue_async_action')) {
            return;
        }

        $thumb_id = (int) get_post_thumbnail_id($post_id);

        if ($thumb_id === 0) {
            return;
        }

        as_enqueue_async_action('rs_warmup_responsive_images', ['attachment_id' => $thumb_id]);
    }

    /**
     * Worker: pide al proxy los anchos principales de la imagen. Con la
     * caché de disco del proxy, estas peticiones materializan las
     * variantes y calientan la CDN edge para los picos de tráfico.
     */
    public function warmup_attachment(int $attachment_id): void
    {
        /** @var array<string, mixed> $warmup */
        $warmup = rs_array($this->config['warmup'] ?? []);

        /** @var list<int|string> $widths */
        $widths = rs_array($warmup['widths'] ?? [480, 800, 1280]);

        $url = wp_get_attachment_url($attachment_id);

        if (! is_string($url) || $url === '') {
            return;
        }

        foreach ($widths as $width) {
            $variant = self::proxy_url($url, (int) $width);

            if ($variant !== $url) {
                wp_remote_get($variant, ['timeout' => 5, 'sslverify' => false]);
            }
        }
    }
}
