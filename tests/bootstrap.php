<?php

declare(strict_types=1);

/*
 * Bootstrap de la suite de tests unitarios (sin WordPress real).
 *
 * Patchwork DEBE requerirse antes que cualquier clase que luego se
 * redefina (Timber\Timber::*), por eso va primero. Brain Monkey aporta
 * los stubs de funciones de WordPress (get_template_directory, wp_mail...).
 */

require_once __DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php';

require_once __DIR__ . '/../vendor/autoload.php';
