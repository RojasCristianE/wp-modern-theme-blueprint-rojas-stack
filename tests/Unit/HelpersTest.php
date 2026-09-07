<?php

declare(strict_types=1);

use function Brain\Monkey\Functions\when;

afterEach(function () {
    if (is_file(FIXTURE_THEME . '/hot')) {
        unlink(FIXTURE_THEME . '/hot');
    }
});

it('rs_asset delega en AssetServiceProvider en desarrollo', function () {
    when('get_template_directory')->justReturn(FIXTURE_THEME);

    touch(FIXTURE_THEME . '/hot');

    expect(rs_asset('resources/styles/main.css'))
        ->toBe('http://localhost:5173/resources/styles/main.css');
});

it('rs_asset delega en AssetServiceProvider en producción', function () {
    when('get_template_directory')->justReturn(FIXTURE_THEME);
    when('get_template_directory_uri')->justReturn(THEME_URI);

    expect(rs_asset('resources/scripts/main.js'))
        ->toBe(THEME_URI . '/dist/assets/main-abc123.js');
});
