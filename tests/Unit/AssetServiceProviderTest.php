<?php

declare(strict_types=1);

use RojasStack\Providers\AssetServiceProvider;
use function Brain\Monkey\Functions\when;

afterEach(function () {
    if (is_file(FIXTURE_THEME . '/hot')) {
        unlink(FIXTURE_THEME . '/hot');
    }
});

it('resuelve assets contra el servidor Vite en desarrollo (hot file presente)', function () {
    when('get_template_directory')->justReturn(FIXTURE_THEME);

    touch(FIXTURE_THEME . '/hot');

    expect(AssetServiceProvider::get_asset_url('resources/scripts/main.js'))
        ->toBe('http://localhost:5173/resources/scripts/main.js');
});

it('resuelve assets desde el manifest en producción', function () {
    when('get_template_directory')->justReturn(FIXTURE_THEME);
    when('get_template_directory_uri')->justReturn(THEME_URI);

    expect(AssetServiceProvider::get_asset_url('resources/scripts/main.js'))
        ->toBe(THEME_URI . '/dist/assets/main-abc123.js');
});

it('resuelve CSS desde el manifest en producción', function () {
    when('get_template_directory')->justReturn(FIXTURE_THEME);
    when('get_template_directory_uri')->justReturn(THEME_URI);

    expect(AssetServiceProvider::get_asset_url('resources/styles/main.css'))
        ->toBe(THEME_URI . '/dist/assets/main-ghi789.css');
});

it('devuelve cadena vacía si el asset no está en el manifest', function () {
    when('get_template_directory')->justReturn(FIXTURE_THEME);
    when('get_template_directory_uri')->justReturn(THEME_URI);

    expect(AssetServiceProvider::get_asset_url('resources/scripts/no-existe.js'))
        ->toBe('');
});

it('devuelve cadena vacía si no existe manifest.json', function () {
    when('get_template_directory')->justReturn(sys_get_temp_dir() . '/rs-sin-manifest-' . bin2hex(random_bytes(4)));
    when('get_template_directory_uri')->justReturn(THEME_URI);

    expect(AssetServiceProvider::get_asset_url('resources/scripts/main.js'))
        ->toBe('');
});
