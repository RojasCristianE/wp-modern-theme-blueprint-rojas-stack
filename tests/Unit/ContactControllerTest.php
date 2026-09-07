<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use RojasStack\Controllers\ContactController;
use function Brain\Monkey\Functions\when;

afterEach(function () {
    $_POST = [];
});

it('registra los hooks AJAX para usuarios autenticados y anónimos', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new ContactController())->register();

    expect($hooks)->toHaveKeys([
        'wp_ajax_submit_contact',
        'wp_ajax_nopriv_submit_contact',
        'wp_ajax_get_contact_form',
        'wp_ajax_nopriv_get_contact_form',
    ])
        ->and($hooks['wp_ajax_submit_contact'][1])->toBe('handle_submit')
        ->and($hooks['wp_ajax_nopriv_submit_contact'][1])->toBe('handle_submit')
        ->and($hooks['wp_ajax_get_contact_form'][1])->toBe('render_form_fragment')
        ->and($hooks['wp_ajax_nopriv_get_contact_form'][1])->toBe('render_form_fragment');
});

it('renderiza contact.twig con título en el contexto', function () {
    when('__')->returnArg();

    $captured = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$captured) {
        $captured = [$template, $context];
    });

    (new ContactController())->render_page();

    expect($captured[0])->toBe('contact.twig')
        ->and($captured[1]['title'])->toBe('Contáctanos');
});

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
        (new ContactController())->handle_submit();
        $this->fail('wp_die() debería haber terminado la ejecución');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('WP_DIE');
    }

    expect($rendered[0])->toBe('partials/contact-form.twig')
        ->and($rendered[1]['errors'])->toBeArray()
        ->and($rendered[1]['old'])->toBe($_POST);
});

it('envía el correo y muestra el mensaje de éxito cuando la validación pasa', function () {
    when('__')->returnArg();
    when('get_option')->justReturn('admin@site.test');
    when('get_bloginfo')->justReturn('http://site.test');

    $mail = null;

    Functions\expect('wp_mail')->once()->andReturnUsing(function (string $to, string $subject, string $body) use (&$mail) {
        $mail = [$to, $subject, $body];
    });
    Functions\expect('wp_die')->once();

    $this->expectOutputRegex('/Mensaje Enviado/');

    $_POST = [
        'name'    => 'Cristian Rojas',
        'email'   => 'cristian@rojas.test',
        'message' => 'Hola, este mensaje tiene más de diez caracteres.',
    ];

    (new ContactController())->handle_submit();

    expect($mail[0])->toBe('admin@site.test')
        ->and($mail[1])->toContain('Cristian Rojas')
        ->and($mail[2])->toContain('cristian@rojas.test')
        ->and($mail[2])->toContain('Hola, este mensaje');
});

it('usa el destinatario configurado en features.php si no es admin_email', function () {
    when('__')->returnArg();
    when('get_option')->justReturn('admin@site.test');
    when('get_bloginfo')->justReturn('http://site.test');

    $to = null;

    Functions\expect('wp_mail')->once()->andReturnUsing(function (string $recipient, string $subject, string $body) use (&$to) {
        $to = $recipient;
    });
    Functions\expect('wp_die')->once();

    $_POST = [
        'name'    => 'Cristian Rojas',
        'email'   => 'cristian@rojas.test',
        'message' => 'Hola, este mensaje tiene más de diez caracteres.',
    ];

    (new ContactController(['contact_recipient' => 'hola@rojas.test']))->handle_submit();

    expect($to)->toBe('hola@rojas.test');
});
