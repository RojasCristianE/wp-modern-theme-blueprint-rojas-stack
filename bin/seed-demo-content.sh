#!/usr/bin/env bash
#
# Seed de contenido demo para verificar la paginación HTMX de archives
# (categoría y etiqueta). Crea 15 posts con distribución de términos
# pensada para que haya varias páginas: con POSTS_PER_PAGE=6 la home
# del blog queda en 3 páginas, la categoría 'rojas-stack' en 2 (6+4) y
# la etiqueta 'htmx' en 2 (6+1).
#
# POSTS_PER_PAGE (.env) gobierna TODAS las superficies, incluidos los
# archives nativos (hook pre_get_posts en ThemeSetup): los recuentos
# de páginas anteriores son válidos para cualquier valor del env (la
# distribución de términos es la que determina cuántas páginas hay).
#
# Uso:  bash bin/seed-demo-content.sh   (idempotente: aborta si ya corrió)

set -euo pipefail

wp() { ddev wp --path=/var/www/wp-core --allow-root "$@" 2>/dev/null; }

MARKER=$(wp option get rs_seed_demo_marker 2>/dev/null || true)
if [ "${MARKER}" = "1" ]; then
    echo "Seed ya aplicado (option rs_seed_demo_marker=1). Nada que hacer."
    exit 0
fi

# ── Imagen destacada compartida (pipeline de media, spec §12.1) ──
# Una imagen GD real importada una vez y adjuntada a todos los posts:
# el pipeline (webp, srcset, proxy imgproxy) se verifica contra datos
# reales sin inflar la mediateca con 15 archivos gemelos.
ATT_ID=$(wp option get rs_seed_demo_attachment 2>/dev/null | grep -oE '^[0-9]+' | tail -n 1 || true)
if [ -z "${ATT_ID}" ]; then
    echo "Generando imagen destacada demo (GD)…"
    ddev exec php /var/www/html/bin/generate-demo-image.php >/dev/null
    ATT_ID=$(wp media import /tmp/rs-demo.png --porcelain | grep -oE '^[0-9]+' | tail -n 1)
    ddev exec rm -f /tmp/rs-demo.png >/dev/null 2>&1 || true
    wp option update rs_seed_demo_attachment "${ATT_ID}" >/dev/null
    echo "  ✓ attachment ${ATT_ID}"
fi

echo "Creando categorías…"
wp term create category 'Rojas Stack' --slug=rojas-stack >/dev/null || true
wp term create category 'Tutoriales' --slug=tutoriales >/dev/null || true

echo "Creando etiquetas…"
for tag in htmx alpine timber; do
    wp term create post_tag "${tag}" >/dev/null 2>&1 || true
done

echo "Creando 15 posts…"
TITLES=(
    "Patrones de configuración code-first con Carbon Fields"
    "HTMX en WordPress: el contrato de fragmentos"
    "Timber 2 y la jerarquía de plantillas"
    "Tailwind v4 con tokens semánticos y dark mode"
    "Paginación sin recargar: fragmentos con hx-swap"
    "Formularios HTMX con validación Rakit"
    "Micro-interacciones efímeras con Alpine.js"
    "Vite y vite-for-wp: assets modernos en el tema"
    "Testing del tema con Pest y Brain Monkey"
    "Playwright para e2e en WordPress"
    "Lefthook: quality gates en el pre-commit"
    "Rutas virtuales vs jerarquía de templates"
    "SEO code-first con spatie/schema-org"
    "DDEV como entorno local reproducible"
    "Deploys theme-only con Deployer"
)

TAGSETS=("htmx timber" "htmx alpine" "timber" "alpine" "htmx" "htmx" "alpine timber" "timber" "htmx timber" "alpine" "htmx" "timber" "htmx" "alpine timber" "timber")

i=0
for title in "${TITLES[@]}"; do
    i=$((i + 1))
    if [ $((i % 3)) -eq 0 ]; then cat_id=tutoriales; else cat_id=rojas-stack; fi

    # Idempotencia: si el post con slug demo-N ya existe, solo
    # re-aseguramos sus términos y seguimos.
    existing=$(wp post list --post_type=post --name="demo-${i}" --field=ID --post_status=publish | grep -oE '^[0-9]+' | tail -n 1 || true)
    if [ -z "${existing}" ]; then
        date=$(printf '2026-08-%02d 10:00:00' "$i")
        post_id=$(wp post create \
            --post_title="${title}" \
            --post_name="demo-${i}" \
            --post_status=publish \
            --post_date="${date}" \
            --post_category=0 \
            --porcelain | grep -oE '^[0-9]+' | tail -n 1 || true)
    else
        post_id=${existing}
    fi

    # El phar de wp-cli emite notices PHP Deprecated en stdout: el ID
    # porcelain se extrae con grep para no arrastrar ruido.
    wp post term set "${post_id}" category "${cat_id}" >/dev/null
    # shellcheck disable=SC2086
    wp post term set "${post_id}" post_tag ${TAGSETS[$((i - 1))]} >/dev/null
    # Featured image real para el pipeline de media (idempotente).
    wp post meta set "${post_id}" _thumbnail_id "${ATT_ID}" >/dev/null
    echo "  ✓ demo-${i} [${cat_id}]"
done

wp option update rs_seed_demo_marker 1 >/dev/null
echo "Listo: 15 posts, 3 categorías, 3 etiquetas, featured image compartida (attachment ${ATT_ID})."
