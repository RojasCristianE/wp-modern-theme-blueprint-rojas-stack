<?php

declare(strict_types=1);

use RojasStack\Providers\ExampleCptServiceProvider;
use function Brain\Monkey\Functions\when;

it('registra el hook init al pasar por register()', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new ExampleCptServiceProvider())->register();

    expect($hooks)->toHaveKey('init');

    $hook = $hooks['init'] ?? null;
    expect($hook)->toBeArray()
        ->and(is_array($hook) ? $hook[1] : null)->toBe('register_post_type');
});

it('registra el CPT con slug, labels y archive de features.php', function () {
    when('__')->returnArg();
    when('get_option')->justReturn(false);
    when('update_option')->justReturn(true);
    when('flush_rewrite_rules')->justReturn(null);

    $registered = null;

    when('register_post_type')->alias(function (string $slug, array $args = []) use (&$registered) {
        $registered = [$slug, $args];
    });

    (new ExampleCptServiceProvider([
        'slug'     => 'podcast',
        'singular' => 'Podcast',
        'plural'   => 'Podcasts',
    ]))->register_post_type();

    expect($registered[0])->toBe('podcast')
        ->and($registered[1]['has_archive'])->toBeTrue()
        ->and($registered[1]['public'])->toBeTrue()
        ->and($registered[1]['rewrite']['slug'])->toBe('podcast')
        ->and($registered[1]['labels']['name'])->toBe('Podcasts')
        ->and($registered[1]['labels']['singular_name'])->toBe('Podcast')
        ->and($registered[1]['menu_icon'])->toBe('dashicons-microphone');
});

it('ejecuta el flush de rewrites una sola vez', function () {
    when('__')->returnArg();
    when('register_post_type')->justReturn(null);
    when('update_option')->justReturn(true);

    $flushes = 0;
    $optionsRead = [];

    when('get_option')->alias(function (string $option) use (&$optionsRead, &$flushes) {
        $optionsRead[] = $option;

        return (bool) $flushes; // segunda vez: ya fue flusheado
    });
    when('flush_rewrite_rules')->alias(function () use (&$flushes) {
        $flushes++;
    });

    $cpt = new ExampleCptServiceProvider(['slug' => 'podcast']);
    $cpt->register_post_type();
    $cpt->register_post_type();

    expect($flushes)->toBe(1)
        ->and($optionsRead)->toBe(['rs_podcast_cpt_flushed', 'rs_podcast_cpt_flushed']);
});
