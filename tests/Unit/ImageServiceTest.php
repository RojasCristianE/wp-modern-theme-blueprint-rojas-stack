<?php

declare(strict_types=1);

use RojasStack\Services\ImageService;
use function Brain\Monkey\Functions\when;

/*
 * Tests del pipeline rs_image (spec §12.1): markup LCP vs lazy, CLS,
 * fallback nativo (sin srcset) vs proxy (srcset de buckets), y escape.
 */

const IMG_BASE = 'http://theme.test/wp-content/uploads/2026/09/foto.jpg';

beforeEach(function () {
    unset($_ENV['IMGPROXY_ROUTE'], $_SERVER['IMGPROXY_ROUTE']);

    when('wp_upload_dir')->alias(static fn (): array => [
        'baseurl' => 'http://theme.test/wp-content/uploads',
        'basedir' => '/var/www/wp-core/wp-content/uploads',
    ]);
    when('esc_attr')->alias(static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES));

    // El resize nativo escribe en uploads/ (no es territorio unit):
    // el seam se redefine con Patchwork para devolver URL canónica.
    \Patchwork\redefine(
        [ImageService::class, 'native_resize'],
        static fn (string $src, int $width, int $height): string => $src,
    );
});

/**
 * Attachment Timber mínimo (solo lo que ImageService consume).
 */
function img_attachment(string $src = IMG_BASE, int $width = 2400, int $height = 1600): object
{
    return new class($src, $width, $height) {
        public function __construct(
            public string $src,
            public int $width,
            public int $height,
        ) {
        }

        public function __get(string $name): string
        {
            return $name === 'alt' ? 'Foto demo' : '';
        }
    };
}

it('slot card sin proxy: resize nativo, lazy y sin srcset', function () {
    when('get_template_directory')->justReturn('/tmp/fixture-theme');

    $html = ImageService::render(img_attachment(), 'card');

    expect($html)->toContain('src=')
        ->and($html)->toContain('loading="lazy"')
        ->and($html)->toContain('decoding="async"')
        ->and($html)->toContain('sizes="auto"')
        ->and($html)->not->toContain('srcset=')
        ->and($html)->not->toContain('fetchpriority');
});

it('slot hero: LCP con fetchpriority y sin lazy', function () {
    $html = ImageService::render(img_attachment(), 'hero');

    expect($html)->toContain('fetchpriority="high"')
        ->and($html)->not->toContain('loading=')
        ->and($html)->toContain('width="1200"')
        ->and($html)->toContain('height="675"');
});

it('con proxy: srcset de buckets >= base del slot', function () {
    $_ENV['IMGPROXY_ROUTE'] = 'https://site.com/cdn-cgi/image';

    $html = ImageService::render(img_attachment(), 'card');

    expect($html)->toContain('srcset=')
        ->and($html)->toContain('/cdn-cgi/image/w:640/wp-content/uploads/2026/09/foto.jpg 640w')
        ->and($html)->toContain('/cdn-cgi/image/w:1920/wp-content/uploads/2026/09/foto.jpg 1920w');
});

it('atributos extra y alt override se aplican escapados', function () {
    $html = ImageService::render(img_attachment(), 'card', [
        'class' => 'w-full h-48 object-cover',
        'alt'   => 'Título con "comillas" & símbolos',
    ]);

    expect($html)->toContain('class="w-full h-48 object-cover"')
        ->and($html)->toContain('alt="Título con &quot;comillas&quot; &amp; símbolos"');
});

it('retorna vacío para slot desconocido o attachment nulo', function () {
    expect(ImageService::render(null, 'card'))->toBe('')
        ->and(ImageService::render(img_attachment(), 'inexistente'))->toBe('');
});

it('slot full usa las dimensiones del attachment para CLS', function () {
    $html = ImageService::render(img_attachment(width: 2400, height: 1600), 'full');

    expect($html)->toContain('width="2400"')
        ->and($html)->toContain('height="1600"')
        ->and($html)->toContain('fetchpriority="high"');
});
