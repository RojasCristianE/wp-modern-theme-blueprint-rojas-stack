#!/usr/bin/env bash
#
# Reindexado bulk de contenido hacia Meilisearch (bin/meili-reindex.sh).
#
# Recorre TODOS los posts públicos y los envía al índice usando la
# misma lógica de documento que MeilisearchIndexer (hooks de save_post),
# vía eval-file de WP-CLI. Uso:
#
#   bash bin/meili-reindex.sh            # usa MEILI_HOST/.env del tema
#   MEILI_HOST=http://... bash bin/meili-reindex.sh
#
# Idempotente: reenvía todo el contenido (upsert de Meilisearch).

set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [ -f "$THEME_DIR/.env" ]; then
  # shellcheck disable=SC1091
  set -a; source "$THEME_DIR/.env"; set +a
fi

: "${MEILI_HOST:?MEILI_HOST no definido (configura .env o expórtalo)}"
: "${MEILI_API_KEY:?MEILI_API_KEY no definido}"

echo "→ Reindexando hacia $MEILI_HOST (índice: posts)…"

ddev exec env MEILI_HOST="$MEILI_HOST" MEILI_API_KEY="$MEILI_API_KEY" wp --path=/var/www/wp-core --allow-root eval-file - <<'PHP'
<?php
$host = getenv('MEILI_HOST');
$key  = getenv('MEILI_API_KEY');
$engine = new RojasStack\Services\MeilisearchClient($host, $key);

if (! $engine->is_available()) {
    WP_CLI::error('Meilisearch no responde en ' . $host);
}

$query = new WP_Query([
    'post_type'           => 'any',
    'post_status'         => 'publish',
    'posts_per_page'      => 100,
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
]);

$docs = [];
foreach ($query->posts as $post) {
    $docs[] = (new RojasStack\Services\MeilisearchIndexer())->document((int) $post->ID, $post);
}

if ($docs === []) {
    WP_CLI::warning('No hay posts públicos para indexar.');
    return;
}

if ($engine->index($docs)) {
    WP_CLI::success(count($docs) . ' documentos enviados a Meilisearch.');
} else {
    WP_CLI::error('Fallo el envío del lote.');
}
PHP

echo "→ OK. Verifica con: curl $MEILI_HOST/indexes/posts/stats"
