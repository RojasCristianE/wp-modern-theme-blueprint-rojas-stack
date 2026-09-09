<?php

declare(strict_types=1);

/*
 * Contrato del archivo de configuración central (spec §5).
 * Si alguien cambia los estados por defecto, este test lo hace visible.
 *
 * Los valores de features.php llegan como mixed (L10 exige tipo
 * explícito): se normalizan con las guardas rs_* del stack.
 */

it('expone los módulos del spec §5 con sus estados por defecto', function () {
    // design_system depende de $_ENV['WP_ENV'] y posts_per_page de
    // $_ENV['POSTS_PER_PAGE']: aislamos el entorno del test.
    $envBackup = $_ENV;
    unset($_ENV['WP_ENV'], $_ENV['POSTS_PER_PAGE']);

    /** @var array<string, mixed> $features Contrato de features.php */
    $features = require __DIR__ . '/../../app/Config/features.php';

    $_ENV = $envBackup;

    $blog     = rs_array($features['blog'] ?? []);
    $pages    = rs_array($features['pages'] ?? []);
    $forms    = rs_array($features['forms'] ?? []);
    $seo      = rs_array($features['seo'] ?? []);
    $cpt      = rs_array($features['example_cpt'] ?? []);
    $design   = rs_array($features['design_system'] ?? []);
    $contact  = rs_array(rs_array($forms['forms'] ?? [])['contact'] ?? []);

    expect(rs_int($features['posts_per_page'] ?? 0))->toBe(6)
        ->and($blog['enabled'])->toBeTrue()
        ->and(rs_int($blog['posts_per_page'] ?? 0))->toBe(6)
        ->and($pages['enabled'])->toBeTrue()
        ->and($forms['enabled'])->toBeTrue()
        ->and($contact['recipient'])->toBe('admin_email')
        ->and($seo['enabled'])->toBeTrue()
        ->and($seo['organization_name'])->toBeNull()
        ->and($cpt['enabled'])->toBeFalse()
        ->and($design['enabled'])->toBeFalse();
});

it('POSTS_PER_PAGE gobierna blog y el valor global', function () {
    $envBackup = $_ENV;
    $_ENV['POSTS_PER_PAGE'] = '9';
    unset($_ENV['WP_ENV']);

    /** @var array<string, mixed> $features Contrato de features.php */
    $features = require __DIR__ . '/../../app/Config/features.php';

    $_ENV = $envBackup;

    $blog = rs_array($features['blog'] ?? []);

    expect(rs_int($features['posts_per_page'] ?? 0))->toBe(9)
        ->and(rs_int($blog['posts_per_page'] ?? 0))->toBe(9);
});
