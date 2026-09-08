<?php

declare(strict_types=1);

namespace RojasStack\Services;

/**
 * MeilisearchIndexer
 *
 * Sincroniza posts de WP hacia Meilisearch vía hooks nativos
 * (save_post / delete_post) — regla de integridad del stack: la
 * escritura pasa por los hooks de WP, el ORM sigue siendo read-only.
 *
 * Indexa tipos públicos con contenido buscable. Acciones post-layout
 * (template_redirect) para no indexar autodrafts ni basura de editor;
 * el bulk inicial lo hace bin/meili-reindex.sh (WP-CLI).
 */
class MeilisearchIndexer
{
    public function register(): void
    {
        add_action('save_post', [$this, 'handle_save'], 20, 2);
        add_action('delete_post', [$this, 'handle_delete'], 10, 1);
    }

    /**
     * Indexa el post (upsert) tras save.
     *
     * @param  int      $post_id
     * @param  \WP_Post $post
     */
    public function handle_save($post_id, $post): void
    {
        if (! $this->should_index($post)) {
            return;
        }

        $this->engine()->index([$this->document((int) $post_id, $post)]);
    }

    /**
     * Elimina el documento del índice (incluye trash → delete_post).
     */
    public function handle_delete(int $post_id): void
    {
        $this->engine()->delete($post_id);
    }

    /**
     * Documento de índice a partir de un WP_Post.
     *
     * @return array<string, mixed>
     */
    public function document(int $post_id, \WP_Post $post): array
    {
        return [
            'id'           => $post_id,
            'title'        => (string) $post->post_title,
            'content'      => wp_strip_all_tags((string) $post->post_content),
            'excerpt'      => (string) ($post->post_excerpt !== ''
                ? $post->post_excerpt
                : wp_html_excerpt(wp_strip_all_tags((string) $post->post_content), 180, '…')),
            'url'          => (string) get_permalink($post),
            'thumbnail'    => (string) get_the_post_thumbnail_url($post, 'thumbnail'),
            'type'         => (string) $post->post_type,
            'published_at' => (int) get_post_timestamp($post) ?: 0,
        ];
    }

    /**
     * Filtro de elegibilidad: públicos, publicados, con título,
     * sin revisiones/autosaves.
     */
    private function should_index(\WP_Post $post): bool
    {
        return ! in_array($post->post_status, ['auto-draft', 'draft', 'pending', 'trash', 'inherit'], true)
            && ! wp_is_post_revision($post)
            && ! wp_is_post_autosave($post)
            && post_type_supports($post->post_type, 'title')
            && get_post_status_object($post->post_status)?->public === true;
    }

    private function engine(): MeilisearchClient
    {
        return new MeilisearchClient();
    }
}
