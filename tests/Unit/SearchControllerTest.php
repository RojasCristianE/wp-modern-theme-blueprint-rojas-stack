<?php

declare(strict_types=1);

use RojasStack\Controllers\SearchController;
use RojasStack\Services\MeilisearchClient;
use Brain\Monkey\Functions;
use function Brain\Monkey\Functions\when;

/*
 * Stubs WP comunes para todos los tests de este archivo (sin fugas
 * entre archivos: self-contained).
 */
beforeEach(function () {
    // Helpers de la HTTP API que usan los clientes (formas idénticas a
    // WP real): por test vía Brain Monkey, NO como funciones reales en
    // Pest.php (Patchwork exige definirlos en runtime).
    when('wp_remote_retrieve_response_code')->alias(
        fn ($response) => is_array($response) && isset($response['response']['code'])
            ? (int) $response['response']['code']
            : 0,
    );
    when('wp_remote_retrieve_body')->alias(
        fn ($response) => is_array($response) && isset($response['body'])
            ? (string) $response['body']
            : '',
    );
    when('is_wp_error')->alias(fn ($thing) => $thing instanceof WP_Error);
});

/*
 * Tests del endpoint de búsqueda en vivo (spec §9.3, contrato de fragmentos).
 * La petición HTMX llega a admin-ajax?action=rs_live_search con el término
 * en 's'; el controller responde SOLO partials/search-results.twig.
 */

afterEach(function () {
    $_GET = [];
});

it('registra el endpoint AJAX para visitantes y usuarios logueados', function () {
    $hooks = [];

    when('add_action')->alias(function (string $hook, callable|array $callback) use (&$hooks) {
        $hooks[$hook] = $callback;
    });

    (new SearchController())->register();

    expect($hooks)->toHaveKeys([
        'wp_ajax_rs_live_search',
        'wp_ajax_nopriv_rs_live_search',
    ])
        ->and($hooks['wp_ajax_rs_live_search'][1])->toBe('render_fragment');
});

it('no consulta la DB si el término está vacío', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_text_field')->returnArg();
    // En WordPress real wp_die() TERMINA la ejecución (mismo patrón que
    // FormControllerTest): el stub lanza para que el flujo no continúe
    // hacia Timber::context()/get_posts() tras el early-return.
    Functions\expect('wp_die')->once()->andReturnUsing(function () {
        throw new RuntimeException('WP_DIE');
    });

    $rendered = null;
    $postsArgs = 'NO_CALLED';
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return [];
    });

    $_GET['s'] = '';

    try {
        (new SearchController())->render_fragment();
        $this->fail('wp_die() debería haber terminado la ejecución');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('WP_DIE');
    }

    expect($postsArgs)->toBe('NO_CALLED', 'no debería consultarse la DB con término vacío')
        ->and($rendered[0])->toBe('partials/search-results.twig')
        ->and($rendered[1]['posts'])->toBe([])
        ->and($rendered[1]['search_query'])->toBe('');
});

it('busca posts y renderiza el fragmento con el término saneado', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_text_field')->alias(fn (string $s) => trim($s));
    when('__')->returnArg();
    Functions\expect('wp_die')->once();

    $postsArgs = null;
    $rendered = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return ['POST_A', 'POST_B'];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });

    $_GET['s'] = '  podman  ';

    (new SearchController())->render_fragment();

    expect($postsArgs['s'])->toBe('podman')
        ->and($postsArgs['posts_per_page'])->toBe(6)
        ->and($rendered[0])->toBe('partials/search-results.twig')
        ->and($rendered[1]['search_query'])->toBe('podman');
});

/*
 * Motor de búsqueda (SearchEngineInterface): Meilisearch client.
 */

it('MeilisearchClient sin credenciales no está disponible y no busca', function () {
    $client = new MeilisearchClient('', 'key');

    expect($client->is_available())->toBeFalse()
        ->and($client->search('vite'))->toBe([])
        ->and($client->index([['id' => 1]]))->toBeFalse()
        ->and($client->delete(1))->toBeFalse();
});

it('MeilisearchClient con credenciales consulta /health', function () {
    $captured = [];

    when('wp_remote_request')->alias(function (string $url, array $args = []) use (&$captured) {
        $captured = [$url, $args];

        return ['body' => '{"status":"available"}'];
    });

    $client = new MeilisearchClient('http://meili.test:7700/', 'secret-key');

    expect($client->is_available())->toBeTrue()
        ->and($captured[0])->toBe('http://meili.test:7700/health')
        ->and($captured[1]['headers']['Authorization'])->toBe('Bearer secret-key')
        ->and($captured[1]['headers']['Content-Type'])->toBe('application/json')
        ->and($captured[1]['timeout'])->toBe(1.5);
});

it('MeilisearchClient::search parsea los hits del motor', function () {
    when('wp_remote_request')->alias(fn () => [
        'body' => '{"hits":[{"id":16,"title":"Vite"},{"id":17,"title":"HTMX"}]}',
    ]);

    $client = new MeilisearchClient('http://meili.test:7700', 'k');
    $hits = $client->search('vite', 5);

    expect($hits)->toHaveCount(2)
        ->and($hits[0]['id'])->toBe(16);
});

it('MeilisearchClient::search devuelve array vacío si el motor falla', function () {
    when('wp_remote_request')->alias(fn () => new WP_Error('http_request_failed', 'timeout'));

    $client = new MeilisearchClient('http://meili.test:7700', 'k');

    expect($client->search('vite'))->toBe([]);
});

it('SearchController usa el motor y conserva el orden de relevancia', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_text_field')->returnArg();
    when('__')->returnArg();
    Functions\expect('wp_die')->once();

    $engine = new class implements RojasStack\Contracts\SearchEngineInterface {
        public function search(string $query, int $limit = 6): array
        {
            return [['id' => 17], ['id' => 16]]; // orden de relevancia del motor
        }

        public function index(array $documents): bool
        {
            return true;
        }

        public function delete(int $post_id): bool
        {
            return true;
        }

        public function is_available(): bool
        {
            return true;
        }
    };

    $postsArgs = null;
    $rendered = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return ['POST_17', 'POST_16'];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });

    $_GET['s'] = 'deployr';

    (new SearchController(6, $engine))->render_fragment();

    expect($postsArgs['post__in'])->toBe([17, 16])
        ->and($postsArgs['orderby'])->toBe('post__in')
        ->and($rendered[1]['posts'])->toBe(['POST_17', 'POST_16']);
});

it('MeilisearchClient::configure declara título > excerpt > contenido como pesos', function () {
    $captured = [];

    when('wp_remote_request')->alias(function (string $url, array $args = []) use (&$captured) {
        $captured[] = [$url, $args];

        return ['body' => '{"taskUid":1}'];
    });

    $client = new MeilisearchClient('http://meili.test:7700', 'write-key');
    $task = $client->configure();

    // captured[0] = health check (is_available), captured[1] = settings PATCH.
    $settingsCall = $captured[1];
    $payload = json_decode((string) $settingsCall[1]['body'], true);

    expect($task)->toBe(['taskUid' => 1])
        ->and($settingsCall[0])->toBe('http://meili.test:7700/indexes/posts/settings')
        ->and($settingsCall[1]['method'])->toBe('PATCH')
        ->and($payload['searchableAttributes'])->toBe(['title', 'excerpt', 'content', 'type'])
        ->and($payload['rankingRules'])->toContain('published_at:desc');
});

it('MeilisearchClient::configure devuelve null si el motor no responde', function () {
    when('wp_remote_request')->alias(fn () => new WP_Error('http_request_failed', 'down'));

    $client = new MeilisearchClient('http://meili.test:7700', 'write-key');

    expect($client->configure())->toBeNull();
});

it('SearchController cae a la búsqueda DB si el motor no está disponible', function () {
    when('wp_unslash')->returnArg();
    when('sanitize_text_field')->returnArg();
    when('__')->returnArg();
    Functions\expect('wp_die')->once();

    $engine = new class implements RojasStack\Contracts\SearchEngineInterface {
        public function search(string $query, int $limit = 6): array
        {
            throw new RuntimeException('no debería llamarse');
        }

        public function index(array $documents): bool
        {
            return false;
        }

        public function delete(int $post_id): bool
        {
            return false;
        }

        public function is_available(): bool
        {
            return false;
        }
    };

    $postsArgs = null;

    \Patchwork\redefine('Timber\Timber::context', fn (): array => []);
    \Patchwork\redefine('Timber\Timber::get_posts', function (array $args = []) use (&$postsArgs) {
        $postsArgs = $args;

        return ['NATIVE_POST'];
    });
    \Patchwork\redefine('Timber\Timber::render', function (string $template, array $context) use (&$rendered) {
        $rendered = [$template, $context];
    });

    $_GET['s'] = 'vite';

    (new SearchController(6, $engine))->render_fragment();

    expect($postsArgs['s'])->toBe('vite')
        ->and(array_key_exists('post__in', $postsArgs))->toBeFalse();
});
