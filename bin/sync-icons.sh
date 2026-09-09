#!/usr/bin/env bash
#
# Sincroniza los sets de iconos desde node_modules hacia resources/icons/
# (búsqueda: 'rs-icons-sync').
#
# Sets (spec §12.1, entrada 8):
#   - line-md    (Material Line Icons, animados vía CSS embebida con guard
#                 de prefers-reduced-motion) — set primario.
#   - material   (@material-design-icons/svg, variante `round` — misma
#                 geometría que line-md) — respaldo estático.
#   - spinner    (svg-spinners, CSS embebida) — estados de carga.
#
# La copia es destructiva por set (rm + cp): las eliminaciones upstream
# se propagan. Los SVGs quedan versionados en el repo: producción nunca
# lee node_modules y el diff del commit muestra exactamente qué cambió.
#
# Uso: bash bin/sync-icons.sh   (requiere `bun install` previo)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC="$ROOT/node_modules"
DEST="$ROOT/resources/icons"

fail() { echo "ERROR: $*" >&2; exit 1; }

command -v cp >/dev/null || fail "entorno sin cp"
[ -d "$SRC/line-md/svg-style" ] || fail "falta line-md — ejecuta 'bun install' primero"
[ -d "$SRC/@material-design-icons/svg/round" ] || fail "falta @material-design-icons/svg — ejecuta 'bun install' primero"
[ -d "$SRC/svg-spinners/svg" ] || fail "falta svg-spinners — ejecuta 'bun install' primero"

sync_set() { # $1 = origen, $2 = nombre del set
    rm -rf "$DEST/$2"
    mkdir -p "$DEST/$2"
    cp "$1"/*.svg "$DEST/$2/"
    printf '  %-10s %s iconos\n' "$2:" "$(ls "$DEST/$2" | wc -l)"
}

mkdir -p "$DEST"
echo "Sincronizando iconos → resources/icons/"
sync_set "$SRC/line-md/svg-style"                  line-md
sync_set "$SRC/@material-design-icons/svg/round"   material
sync_set "$SRC/svg-spinners/svg"                   spinner
echo "OK"
