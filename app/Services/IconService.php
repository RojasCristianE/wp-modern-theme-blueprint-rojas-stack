<?php

/**
 * IconService
 *
 * Renderizado de iconos SVG desde el árbol versionado resources/icons/
 * (búsqueda: 'rs-icons-service').
 *
 * Sets sincronizados por bin/sync-icons.sh (spec §12.1, entrada 8):
 *   - 'heroicons'        24/outline — primario, trazo (Tailwind Labs, MIT).
 *   - 'heroicons-solid'  24/solid — relleno, énfasis/estados activos (MIT).
 *
 * Resolución: 'name' usa el set primario; 'set:name' fuerza un set y, si
 * el icono no existe ahí, hace fallback en cadena al resto del registro.
 *
 * Animación: daisyUI `swap` (intercambio de iconos) y `loading` (spinners)
 * — CSS puro del catálogo, sin runtime ni archivos embebidos.
 * Seguridad: el nombre se normaliza (solo [a-z0-9._-], sin '..' ni
 * separadores de ruta) y los SVG provienen del repo — nunca de input.
 */

declare(strict_types=1);

namespace RojasStack\Services;

class IconService
{
    /** Registro de sets: clave => [directorio, licencia]. Orden = cadena de fallback. */
    private const SETS = [
        'heroicons'       => ['heroicons', 'MIT'],
        'heroicons-solid' => ['heroicons-solid', 'MIT'],
    ];

    private const DEFAULT_SET = 'heroicons';

    /** Caracteres válidos para un nombre de icono (después de normalizar). */
    private const NAME_PATTERN = '/[^a-z0-9._-]/';

    /** @var array<string, string> Caché por petición: clave "set:name" => markup. */
    private static array $cache = [];

    /**
     * Renderiza el SVG del icono solicitado.
     *
     * @param string          $name  'search' o 'set:name'.
     * @param array<string, mixed> $attrs Atributos HTML extra (class, title, data-*, ...).
     *                               Solo se usan los valores scalar; null/'' se descartan.
     *
     * @return string Markup `<svg>` completo, o '' si el icono no existe.
     */
    public static function render(string $name, array $attrs = []): string
    {
        $class = 'w-5 h-5';
        $class_attr = $attrs['class'] ?? null;

        if (is_scalar($class_attr)) {
            $class = (string) $class_attr;
        }

        unset($attrs['class']);

        $key = self::normalize($name);

        if ($key === null) {
            return '';
        }

        $markup = self::$cache[$key] ??= self::load($key);

        if ($markup === '') {
            return '';
        }

        $attributes = 'class="' . esc_attr($class) . '" aria-hidden="true"';

        foreach ($attrs as $attr => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $value = (string) $value;

            if ($value === '') {
                continue;
            }

            $attributes .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
        }

        return str_replace('<svg ', '<svg ' . $attributes . ' ', $markup);
    }

    /**
     * Normaliza y valida el identificador: minúsculas, guiones, sin
     * separadores de ruta. Devuelve "set:name" o null si es inválido.
     */
    private static function normalize(string $name): ?string
    {
        $name = strtolower(trim($name));

        if ($name === '' || str_contains($name, '..')) {
            return null;
        }

        if (str_contains($name, ':')) {
            [$set, $icon] = explode(':', $name, 2);

            if (!isset(self::SETS[$set])) {
                return null;
            }
        } else {
            [$set, $icon] = [self::DEFAULT_SET, $name];
        }

        $icon = (string) preg_replace(self::NAME_PATTERN, '', $icon);

        return $icon === '' ? null : $set . ':' . $icon;
    }

    /**
     * Busca el icono en su set y, si falta, recorre el resto del registro
     * (set pedido primero, luego el orden del registro).
     */
    private static function load(string $key): string
    {
        [$set, $icon] = explode(':', $key, 2);

        $order = [$set, ...array_diff(array_keys(self::SETS), [$set])];

        foreach ($order as $candidate) {
            $path = self::path($candidate, $icon);

            if ($path !== null && is_readable($path)) {
                $contents = trim((string) file_get_contents($path));

                if ($contents !== '' && str_starts_with($contents, '<svg')) {
                    return $contents;
                }
            }
        }

        return '';
    }

    /**
     * Ruta absoluta del SVG dentro de resources/icons/, o null si el
     * nombre no es seguro para usar en una ruta.
     */
    private static function path(string $set, string $icon): ?string
    {
        $dir = self::SETS[$set][0] ?? null;

        if ($dir === null || preg_match(self::NAME_PATTERN, $icon)) {
            return null;
        }

        $base = dirname(__DIR__, 2) . '/resources/icons/' . $dir;

        return $base . '/' . $icon . '.svg';
    }
}
