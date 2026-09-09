<?php

declare(strict_types=1);

use RojasStack\Controllers\ArchiveController;
use Brain\Monkey\Functions;
use function Brain\Monkey\Functions\when;

/*
 * Tests del endpoint del fragmento de archives (categoría/etiqueta/home
 * del blog): admin-ajax?action=rs_archive_page responde SOLO
 * partials/archive-grid.twig (grid + paginación HTMX interior).
 */

afterEach(function () {
    $_GET = [];
});

it('registra el endpoint AJAX para visitantes y usuarios logueados', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new ArchiveController())->register();

    expect($hooks)->toHaveKeys([
        'wp_ajax_rs_archive_page',
        'wp_ajax_nopriv_rs_archive_page',
    ]);

    $hook = $hooks['wp_ajax_rs_archive_page'] ?? null;
    expect($hook)->toBeArray()
        ->and(is_array($hook) ? $hook[1] : null)->toBe('render_fragment');
});

it('consulta por término y pagina, y renderiza el fragmento HTMX', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_key')->returnArg();
    when('sanitize_title')->returnArg();
    when('esc_url_raw')->returnArg();
    when('get_object_taxonomies')->justReturn(['category', 'post_tag', 'post_format']);
    when('admin_url')->alias(fn (string $p = '') => 'http://theme.test/wp-admin/' . $p);
    when('get_term_link')->alias(fn (string $t, ?string $tax = null): string => 'http://theme.test/tag/' . $t);
    when('home_url')->alias(fn (string $p = '') => 'http://theme.test/' . $p);
    when('is_wp_error')->justReturn(false);
    when('__')->returnArg();
    // En WordPress real wp_die() TERMINA la ejecución (patrón de la suite).
    Functions\expect('wp_die')->once()->andReturnUsing(function () {
        throw new RuntimeException('WP_DIE');
    });

    /** @var array<string, mixed> $postsArgs */
    $postsArgs = [];
    $template = '';
    $context  = [];
    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $t, array $c) use (&$template, &$context) {
        $template = $t;
        $context  = $c;
    });

    $_GET = ['taxonomy' => 'post_tag', 'term' => 'htmx', 'paged' => '2'];

    try {
        (new ArchiveController(3))->render_fragment();
        $this->fail('wp_die() debería haber terminado la ejecución');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('WP_DIE');
    }

    $taxQuery = rs_array($postsArgs['tax_query'] ?? []);
    $taxRow    = rs_array($taxQuery[0] ?? []);

    expect($postsArgs['post_type'] ?? null)->toBe('post')
        ->and($postsArgs['paged'] ?? null)->toBe(2)
        ->and($postsArgs['posts_per_page'] ?? null)->toBe(3)
        ->and($taxRow['taxonomy'] ?? null)->toBe('post_tag')
        ->and($taxRow['field'] ?? null)->toBe('slug')
        ->and($taxRow['terms'] ?? null)->toBe('htmx')
        ->and($template)->toBe('partials/archive-grid.twig')
        ->and($context['htmx_enabled'] ?? null)->toBeTrue()
        ->and($context['archive_taxonomy'] ?? null)->toBe('post_tag')
        ->and($context['archive_term'] ?? null)->toBe('htmx')
        ->and(rs_string($context['htmx_endpoint'] ?? ''))->toContain('admin-ajax.php');
});

it('cae a category y no filtra por término con GET inválido o ausente', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_key')->returnArg();
    when('sanitize_title')->returnArg();
    when('esc_url_raw')->returnArg();
    when('get_object_taxonomies')->justReturn(['category', 'post_tag', 'post_format']);
    when('admin_url')->alias(fn (string $p = '') => 'http://theme.test/wp-admin/' . $p);
    when('home_url')->alias(fn (string $p = '') => 'http://theme.test/' . $p);
    when('is_wp_error')->justReturn(false);
    Functions\expect('wp_die')->once()->andReturnUsing(function () {
        throw new RuntimeException('WP_DIE');
    });

    /** @var array<string, mixed> $postsArgs */
    $postsArgs = [];
    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });
    \Patchwork\redefine('Timber\Timber::render', fn (string $t, array $c) => null);

    $_GET = ['taxonomy' => 'eval(', 'term' => ''];

    try {
        (new ArchiveController())->render_fragment();
        $this->fail('wp_die() debería haber terminado la ejecución');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('WP_DIE');
    }

    expect($postsArgs['tax_query'] ?? null)->toBeNull()
        ->and($postsArgs['paged'])->toBe(1)
        ->and($postsArgs['posts_per_page'])->toBe(6);
});
