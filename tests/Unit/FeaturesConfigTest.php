<?php

declare(strict_types=1);

/*
 * Contrato del archivo de configuración central (spec §5).
 * Si alguien cambia los estados por defecto, este test lo hace visible.
 */

it('expone los módulos del spec §5 con sus estados por defecto', function () {
    // design_system depende de $_ENV['WP_ENV']: aislamos el entorno del test
    $envBackup = $_ENV['WP_ENV'] ?? null;
    unset($_ENV['WP_ENV']);

    $features = require __DIR__ . '/../../app/Config/features.php';

    if ($envBackup !== null) {
        $_ENV['WP_ENV'] = $envBackup;
    }

    expect($features)->toBeArray()
        ->and($features['blog']['enabled'])->toBeTrue()
        ->and($features['blog']['posts_per_page'])->toBe(12)
        ->and($features['pages']['enabled'])->toBeTrue()
        ->and($features['forms']['enabled'])->toBeTrue()
        ->and($features['forms']['forms']['contact']['recipient'])->toBe('admin_email')
        ->and($features['seo']['enabled'])->toBeTrue()
        ->and($features['seo']['organization_name'])->toBeNull()
        ->and($features['example_cpt']['enabled'])->toBeFalse()
        ->and($features['design_system']['enabled'])->toBeFalse();
});
