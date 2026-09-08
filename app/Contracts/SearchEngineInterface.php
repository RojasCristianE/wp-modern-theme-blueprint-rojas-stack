<?php

declare(strict_types=1);

namespace RojasStack\Contracts;

/**
 * SearchEngineInterface
 *
 * Contrato para motores de búsqueda intercambiables (Meilisearch,
 * Typesense, ...). El SearchController lo usa como fuente de datos
 * del fragmento en vivo; si el motor no está disponible, el controlador
 * cae a la búsqueda nativa (DB) — el tema NUNCA depende duramente del
 * motor.
 *
 * Regla del stack: la escritura de datos usa hooks nativos de WP
 * (save_post/delete_post vía MeilisearchIndexer), nunca escritura
 * directa del ORM (regla de integridad §1).
 */
interface SearchEngineInterface
{
    /**
     * Busca documentos y devuelve hits en orden de relevancia.
     *
     * @param  string $query Término de búsqueda (fuzzy/typo-tolerant según motor).
     * @param  int    $limit Máximo de resultados.
     * @return array<int, array<string, mixed>> Hits con al menos la llave 'id'
     *         (post ID de WP). Array vacío si no hay resultados o el motor falla.
     */
    public function search(string $query, int $limit = 6): array;

    /**
     * Indexa (upsert) documentos en bulk.
     *
     * @param  array<int, array<string, mixed>> $documents
     * @return bool Éxito del envío (el motor indexa async).
     */
    public function index(array $documents): bool;

    /**
     * Elimina un documento por ID de post.
     */
    public function delete(int $post_id): bool;

    /**
     * ¿Responde el motor? (health check con timeout corto — jamás
     * debe colgar un render de página).
     */
    public function is_available(): bool;
}
