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

/*
 * Pre-carga de rakit/validation con deprecaciones silenciadas (PHP 8.4+
 * emite E_DEPRECATED al compilar clases con params implícitamente
 * nullable; rakit 1.4, de 2020, es su última versión publicada).
 *
 * La emisión es UNA VEZ por archivo cargado: al tocar aquí las clases
 * que validate() usa, las deprecaciones se emiten ANTES de que PHPUnit
 * registre su error handler — ningún evento llega a la suite y Pest no
 * marca tests como deprecated (el printer de Collision no respeta las
 * marcas suppressed/ignoredByBaseline del evento, así que la única
 * solución real es que el evento no ocurra en contexto de test).
 */
$preLoadLevel = error_reporting();
error_reporting(0);

$probe = (new \Rakit\Validation\Validator)->make(['a' => 'x'], ['a' => 'required']);
$probe->validate();

error_reporting($preLoadLevel);
