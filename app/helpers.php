<?php

declare(strict_types=1);

/**
 * Funciones de utilidad global para el Rojas Stack.
 *
 * Nota: rs_asset() fue retirado — la resolución de assets de Vite
 * vive en kucrut/vite-for-wp (AssetServiceProvider, spec §7.1).
 *
 * Guardias de límite (política de tipado, spec §12.1 #6): los
 * superglobales ($_GET, $_POST, $GLOBALS) y los arrays de WordPress
 * son `mixed` para PHPStan L9. Estas helpers convierten y validan en
 * el borde para que controllers/providers nunca operen sobre mixed
 * crudo ni dependan de casts que fallan en runtime (array→string).
 */

/**
 * Normaliza un valor mixed a string seguro: escalares viajan tal cual,
 * Stringable se casta, y todo lo demás (arrays, null) cae al default.
 */
function rs_string(mixed $value, string $default = ''): string
{
    if (is_string($value)) {
        return $value;
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    if ($value instanceof Stringable) {
        return (string) $value;
    }

    return $default;
}

/**
 * Normaliza un valor mixed a array seguro: los únicos arrays reales
 * (features.php, config, superglobales) viajan tal cual; todo lo demás
 * (null, escalar) cae a un array vacío.
 */
/**
 * @return array<array-key, mixed>
 */
function rs_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

/**
 * Normaliza un valor mixed a int seguro: strings numéricas enteras se
 * aceptan (entrada de formularios y query args); todo lo demás cae al
 * default. "5.5" o "abc" NO se truncan — devuelven default.
 */
function rs_int(mixed $value, int $default = 0): int
{
    if (is_int($value)) {
        return $value;
    }

    if (is_float($value)) {
        return (int) $value;
    }

    if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
        return (int) $value;
    }

    return $default;
}
