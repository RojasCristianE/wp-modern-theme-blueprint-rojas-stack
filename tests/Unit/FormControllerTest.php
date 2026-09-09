<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use RojasStack\Controllers\FormController;
use function Brain\Monkey\Functions\when;

/*
 * Tests del motor de formularios genérico (spec paso 7).
 * La instancia 'contact' de features.php define el contrato.
 */

const FORM_CONFIG = [
    'route'            => '/contacto',
    'title'            => 'Contáctanos',
    'page_template'    => 'contact.twig',
    'form_template'    => 'partials/contact-form.twig',
    'success_template' => 'partials/form-success.twig',
    'recipient'        => 'admin_email',
    'subject'          => 'Nuevo mensaje de: :name',
    'fields'           => [
        'name'    => 'required|min:3',
        'email'   => 'required|email',
        'message' => 'required|min:10',
    ],
    'success'          => [
        'title'   => '¡Mensaje Enviado!',
        'message' => 'Tu mensaje ha sido capturado por Mailpit con éxito.',
        'target'  => '#contact-form-wrapper',
    ],
];

afterEach(function () {
    $_POST = [];
});

it('deriva los hooks AJAX de la clave del formulario', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new FormController('contact', FORM_CONFIG))->register();

    expect($hooks)->toHaveKeys([
        'wp_ajax_submit_contact',
        'wp_ajax_nopriv_submit_contact',
        'wp_ajax_get_form_contact',
        'wp_ajax_nopriv_get_form_contact',
    ]);
    $submit = $hooks['wp_ajax_submit_contact'] ?? null;
    $render = $hooks['wp_ajax_get_form_contact'] ?? null;

    expect($submit)->toBeArray()
        ->and(is_array($submit) ? $submit[1] : null)->toBe('handle_submit')
        ->and($render)->toBeArray()
        ->and(is_array($render) ? $render[1] : null)->toBe('render_fragment');
});

it('renderiza la página configurada con el título del formulario', function () {
    when('__')->returnArg();

    $captured = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$captured) {
        $captured = [$template, $context];
    });

    (new FormController('contact', FORM_CONFIG))->render_page();

    expect($captured[0])->toBe('contact.twig')
        ->and($captured[1]['title'])->toBe('Contáctanos');
});

// Nota: rakit/validation 1.4 (2020) emite E_DEPRECATED al compilar sus
// clases en PHP 8.4+. Se pre-carga en tests/bootstrap.php con
// error_reporting(0) para que la emisión ocurra fuera del contexto de
// test — ningún evento llega a PHPUnit/Pest.
it('re-renderiza el formulario con errores cuando la validación falla', function () {
    when('__')->returnArg();

    $rendered = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });

    Functions\expect('wp_mail')->never();
    // En WordPress real wp_die() TERMINA la ejecución: el stub lo simula
    // lanzando, para que el flujo no continúe hacia el envío de correo.
    Functions\expect('wp_die')->once()->andReturnUsing(function () {
        throw new RuntimeException('WP_DIE');
    });

    $_POST = [
        'name'    => 'J',
        'email'   => 'no-es-email',
        'message' => 'corto',
    ];

    try {
        (new FormController('contact', FORM_CONFIG))->handle_submit();
        $this->fail('wp_die() debería haber terminado la ejecución');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('WP_DIE');
    }

    expect($rendered[0])->toBe('partials/contact-form.twig')
        ->and($rendered[1]['errors'])->toBeArray()
        ->and($rendered[1]['old'])->toBe($_POST);
});

it('envía el correo con todos los campos y renderiza el fragmento de éxito', function () {
    when('__')->returnArg();
    when('get_option')->justReturn('admin@site.test');
    when('get_bloginfo')->justReturn('http://site.test');

    $mail = null;
    $rendered = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });

    Functions\expect('wp_mail')->once()->andReturnUsing(function (string $to, string $subject, string $body) use (&$mail) {
        $mail = [$to, $subject, $body];
    });
    Functions\expect('wp_die')->once();

    $_POST = [
        'name'    => 'Cristian Rojas',
        'email'   => 'cristian@rojas.test',
        'message' => 'Hola, este mensaje tiene más de diez caracteres.',
    ];

    (new FormController('contact', FORM_CONFIG))->handle_submit();

    expect($mail[0])->toBe('admin@site.test')
        ->and($mail[1])->toBe('Nuevo mensaje de: Cristian Rojas')
        ->and($mail[2])->toContain('Name: Cristian Rojas')
        ->and($mail[2])->toContain('Email: cristian@rojas.test')
        ->and($mail[2])->toContain('Message: Hola, este mensaje')
        ->and($rendered[0])->toBe('partials/form-success.twig')
        ->and($rendered[1]['success']['fragment_url'])->toContain('action=get_form_contact')
        ->and($rendered[1]['success']['target'])->toBe('#contact-form-wrapper');
});

it('usa el destinatario configurado si no es admin_email', function () {
    when('__')->returnArg();
    when('get_option')->justReturn('admin@site.test');
    when('get_bloginfo')->justReturn('http://site.test');

    $to = null;
    $rendered = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = $template;
    });

    Functions\expect('wp_mail')->once()->andReturnUsing(function (string $recipient) use (&$to) {
        $to = $recipient;
    });
    Functions\expect('wp_die')->once();

    $_POST = [
        'name'    => 'Cristian Rojas',
        'email'   => 'cristian@rojas.test',
        'message' => 'Hola, este mensaje tiene más de diez caracteres.',
    ];

    (new FormController('contact', array_merge(FORM_CONFIG, ['recipient' => 'hola@rojas.test'])))->handle_submit();

    expect($to)->toBe('hola@rojas.test');
});

it('un segundo formulario deriva sus propios hooks sin colisionar', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new FormController('newsletter', ['fields' => ['email' => 'required|email']]))->register();

    expect($hooks)->toHaveKeys([
        'wp_ajax_submit_newsletter',
        'wp_ajax_nopriv_submit_newsletter',
        'wp_ajax_get_form_newsletter',
        'wp_ajax_nopriv_get_form_newsletter',
    ]);
});
