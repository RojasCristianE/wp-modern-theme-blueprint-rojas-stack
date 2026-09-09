<?php

declare(strict_types=1);

namespace RojasStack\Services;

use RojasStack\Contracts\SearchEngineInterface;

/**
 * MeilisearchClient
 *
 * Implementación de SearchEngineInterface sobre la API REST de
 * Meilisearch usando wp_remote_* — cero dependencias Composer nuevas
 * (soberanía: un binario en Docker + HTTP nativo de WP).
 *
 * Credenciales por .env: MEILI_HOST + MEILI_API_KEY (write key o master).
 * Si faltan o el motor no responde, is_available() devuelve false y el
 * SearchController cae a la búsqueda nativa — nunca rompe el render.
 *
 * Timeout corto (1500 ms): una búsqueda en vivo no puede colgar más
 * que eso; el fallback nativo es invisible para el usuario.
 */
class MeilisearchClient implements SearchEngineInterface
{
    private const TIMEOUT_SECONDS = 1.5;

    private string $host;
    private string $api_key;
    private string $index_uid;

    public function __construct(
        ?string $host = null,
        ?string $api_key = null,
        string $index_uid = 'posts',
    ) {
        $this->host      = rtrim(rs_string($host ?? ($_ENV['MEILI_HOST'] ?? $_SERVER['MEILI_HOST'] ?? '')), '/');
        $this->api_key   = rs_string($api_key ?? ($_ENV['MEILI_API_KEY'] ?? $_SERVER['MEILI_API_KEY'] ?? ''));
        $this->index_uid = $index_uid;
    }

    public function search(string $query, int $limit = 6): array
    {
        if ($query === '' || ! $this->is_available()) {
            return [];
        }

        $response = $this->request(
            'POST',
            "/indexes/{$this->index_uid}/search",
            ['q' => $query, 'limit' => $limit],
        );

        if (is_wp_error($response)) {
            return [];
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $hits = is_array($body) && is_array($body['hits'] ?? null) ? $body['hits'] : [];

        /** @var array<int, array<string, mixed>> $hits Meili garantiza
         * hits como lista de objetos → arrays asociativos string-keyed. */
        return $hits;
    }

    public function index(array $documents): bool
    {
        if ($documents === [] || ! $this->is_available()) {
            return false;
        }

        $response = $this->request(
            'POST',
            "/indexes/{$this->index_uid}/documents",
            $documents,
            ['Content-Type' => 'application/json'],
        );

        return ! is_wp_error($response)
            && wp_remote_retrieve_response_code($response) < 300;
    }

    public function delete(int $post_id): bool
    {
        if (! $this->is_available()) {
            return false;
        }

        $response = $this->request(
            'DELETE',
            "/indexes/{$this->index_uid}/documents/{$post_id}",
        );

        return ! is_wp_error($response)
            && wp_remote_retrieve_response_code($response) < 300;
    }

    public function is_available(): bool
    {
        if ($this->host === '' || $this->api_key === '') {
            return false;
        }

        // Health check barato: GET /health no requiere auth en Meilisearch,
        // pero lo mandamos con key por si el deployment la exige.
        $response = $this->request('GET', '/health');

        return ! is_wp_error($response)
            && wp_remote_retrieve_response_code($response) < 300;
    }

    /**
     * Configura el ranking del índice: los atributos buscables se
     * declaran EN ORDEN DE PESO (título > excerpt > contenido, el resto
     * sin peso custom) y el desempate de relevancia idéntica va de lo
     * más reciente a lo más viejo. Idempotente y asíncrono: devuelve el
     * taskUID de Meilisearch o null si falla.
     *
     * @return array{taskUid: int}|null
     */
    public function configure(): ?array
    {
        if (! $this->is_available()) {
            return null;
        }

        $response = $this->request(
            'PATCH',
            "/indexes/{$this->index_uid}/settings",
            [
                'searchableAttributes' => [
                    'title',
                    'excerpt',
                    'content',
                    'type',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'published_at:desc',
                ],
            ],
        );

        if (is_wp_error($response)) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Meili responde PATCH /settings con {taskUid, ...}: validamos el
        // shape en vez de fiarnos de un cast.
        return is_array($body) && isset($body['taskUid']) && is_int($body['taskUid'])
            ? ['taskUid' => $body['taskUid']]
            : null;
    }

    /**
     * HTTP wrapper común (auth + timeouts + error silencioso).
     *
     * @param  array<array-key, mixed>|null $payload
     * @param  array<string, string>        $extra_headers
     * @return array<string, mixed>|\WP_Error
     */
    private function request(string $method, string $path, ?array $payload = null, array $extra_headers = [])
    {
        $args = [
            'method'  => $method,
            'timeout' => self::TIMEOUT_SECONDS,
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $this->api_key,
                // Obligatorio: WP HTTP defaultea x-www-form-urlencoded cuando
                // el body es string y Meilisearch rechaza con HTTP 415.
                'Content-Type'  => 'application/json',
            ], $extra_headers),
        ];

        if ($payload !== null) {
            $args['body'] = (string) json_encode($payload, JSON_UNESCAPED_UNICODE);
        }

        return wp_remote_request($this->host . $path, $args);
    }
}
