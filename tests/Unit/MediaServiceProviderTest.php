<?php

declare(strict_types=1);

use RojasStack\Providers\MediaServiceProvider;
use function Brain\Monkey\Functions\when;

/*
 * Tests del pipeline de media (spec §12.1): buckets cuantizados, URLs
 * del proxy (IMGPROXY_ROUTE), extracción de paths relativos a uploads,
 * webp forzado y recorte de tamaños default.
 */

beforeEach(function () {
    unset($_ENV['IMGPROXY_ROUTE'], $_SERVER['IMGPROXY_ROUTE']);
    unset($_ENV['WARMUP_ENABLED'], $_SERVER['WARMUP_ENABLED']);

    when('wp_upload_dir')->alias(static fn (): array => [
        'baseurl' => 'http://theme.test/wp-content/uploads',
        'basedir' => '/var/www/wp-core/wp-content/uploads',
    ]);
    when('add_filter')->justReturn(null);
    when('add_action')->justReturn(null);
});

it('cuantiza al bucket inmediato superior', function (int $requested, int $expected) {
    expect(MediaServiceProvider::bucket_for($requested))->toBe($expected);
})->with([
    [100, 320],
    [320, 320],
    [321, 480],
    [600, 640],
    [1280, 1280],
    [1921, 1921], // mayor que el bucket máximo: sin upsize
]);

it('construye la URL del proxy con el bucket y el path relativo', function () {
    $_ENV['IMGPROXY_ROUTE'] = 'https://site.com/cdn-cgi/image';

    $url = MediaServiceProvider::proxy_url(
        'http://theme.test/wp-content/uploads/2026/09/foto.jpg',
        600,
    );

    expect($url)->toBe('https://site.com/cdn-cgi/image/w:640/wp-content/uploads/2026/09/foto.jpg');
});

it('devuelve el src intacto sin IMGPROXY_ROUTE', function () {
    $src = 'http://theme.test/wp-content/uploads/2026/09/foto.jpg';

    expect(MediaServiceProvider::proxy_url($src, 600))->toBe($src);
});

it('no toca URLs externas al uploads local', function () {
    $_ENV['IMGPROXY_ROUTE'] = 'https://site.com/cdn-cgi/image';

    $external = 'https://cdn.otro.com/img.jpg';

    expect(MediaServiceProvider::proxy_url($external, 600))->toBe($external);
});

it('extrae el path relativo a uploads', function () {
    $relative = MediaServiceProvider::uploads_relative_path(
        'http://theme.test/wp-content/uploads/2026/09/foto.jpg',
    );

    expect($relative)->toBe('wp-content/uploads/2026/09/foto.jpg');
});

it('fuerza webp en el editor de WP', function () {
    $provider = new MediaServiceProvider(['proxy' => ['enabled' => false]]);

    $formats = $provider->force_webp_output(['image/jpeg' => 'image/jpeg']);

    expect($formats)->toBe([
        'image/jpeg' => 'image/webp',
        'image/png'  => 'image/webp',
    ]);
});

it('recorta los tamaños default a los que el tema consume', function () {
    $provider = new MediaServiceProvider([]);

    /** @var array<string, mixed> $sizes */
    $sizes = ['thumbnail' => 1, 'medium' => 1, 'medium_large' => 1, 'large' => 1, '1536x1536' => 1];

    expect($provider->trim_default_sizes($sizes))->toBe(['medium', 'large']);
});

it('proxy_enabled exige ruta configurada', function () {
    expect((new MediaServiceProvider(['proxy' => ['enabled' => true, 'route' => '']]))->proxy_enabled())->toBe(false)
        ->and((new MediaServiceProvider(['proxy' => ['enabled' => true, 'route' => '/cdn-cgi/image']]))->proxy_enabled())->toBe(true);
});

it('warmup_enabled lee el flag del config', function () {
    expect((new MediaServiceProvider(['warmup' => ['enabled' => false]]))->warmup_enabled())->toBe(false)
        ->and((new MediaServiceProvider(['warmup' => ['enabled' => true]]))->warmup_enabled())->toBe(true);
});

it('warmup_attachment pide los anchos del config al proxy', function () {
    $_ENV['IMGPROXY_ROUTE'] = 'https://site.com/cdn-cgi/image';
    when('wp_get_attachment_url')->justReturn('http://theme.test/wp-content/uploads/2026/09/foto.jpg');

    $calls = [];
    when('wp_remote_get')->alias(function (string $url, array $args = []) use (&$calls) {
        $calls[] = $url;

        return ['body' => 'ok'];
    });

    $provider = new MediaServiceProvider(['warmup' => ['enabled' => true, 'widths' => [480, 1280]]]);
    $provider->warmup_attachment(42);

    expect($calls)->toBe([
        'https://site.com/cdn-cgi/image/w:480/wp-content/uploads/2026/09/foto.jpg',
        'https://site.com/cdn-cgi/image/w:1280/wp-content/uploads/2026/09/foto.jpg',
    ]);
});

it('enqueue_warmup ignora posts sin thumbnail', function () {
    when('get_post_thumbnail_id')->justReturn(0);

    // No debe lanzar ni encolar: sin thumbnail no hay nada que hacer.
    $enqueued = [];
    when('as_enqueue_async_action')->alias(function (string $hook, array $args = []) use (&$enqueued) {
        $enqueued[] = $args;

        return 1;
    });

    (new MediaServiceProvider(['warmup' => ['enabled' => true]]))->enqueue_warmup(7);

    expect($enqueued)->toBe([]);
});
