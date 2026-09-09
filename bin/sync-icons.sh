#!/usr/bin/env bash
#
# Sincroniza los sets de iconos desde node_modules hacia resources/icons/
# (búsqueda: 'rs-icons-sync').
#
# Sets (spec §12.1, entrada 8):
#   - heroicons        (24/outline — trazo, primario; stroke="currentColor")
#   - heroicons-solid  (24/solid — relleno, énfasis/estados activos)
#
# Ambos son el set oficial de Tailwind Labs (MIT), grid 24×24 — la misma
# geometría que asume el catálogo de daisyUI. Animación de iconos: daisyUI
# `swap` (intercambio) y `loading` (spinners) — CSS puro, sin runtime.
#
# La copia es destructiva por set (rm + cp): las eliminaciones upstream
# se propagan. Los SVGs quedan versionados en el repo: producción nunca
# lee node_modules y el diff del commit muestra exactamente qué cambió.
#
# Uso: bash bin/sync-icons.sh   (requiere `bun install` previo)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC="$ROOT/node_modules/heroicons/24"
DEST="$ROOT/resources/icons"

fail() { echo "ERROR: $*" >&2; exit 1; }

[ -d "$SRC/outline" ] || fail "falta heroicons — ejecuta 'bun install' primero"
[ -d "$SRC/solid" ] || fail "falta heroicons — ejecuta 'bun install' primero"

sync_set() { # $1 = origen, $2 = nombre del set
    rm -rf "$DEST/$2"
    mkdir -p "$DEST/$2"
    cp "$1"/*.svg "$DEST/$2/"
    printf '  %-16s %s iconos\n' "$2:" "$(ls "$DEST/$2" | wc -l)"
}

mkdir -p "$DEST"
echo "Sincronizando iconos → resources/icons/"
sync_set "$SRC/outline" heroicons
sync_set "$SRC/solid"   heroicons-solid
echo "OK"
