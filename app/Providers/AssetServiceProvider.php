<?php

namespace RojasStack\Providers;

use function Kucrut\Vite\enqueue_asset;

/**
 * AssetServiceProvider
 * 
 * Encola los assets de Vite vía kucrut/vite-for-wp (spec §7.1).
 * La librería resuelve ambos modos:
 *   - Desarrollo: mientras corre `bun run dev`, existe dist/vite-dev-server.json
 *     y los assets se sirven desde el dev server con HMR.
 *   - Producción: lee dist/manifest.json y encola el bundle con su CSS asociado.
 */
class AssetServiceProvider
{
    private const DIST_DIR = 'dist';
    private const MAIN_ENTRY = 'resources/scripts/main.js';
    private const MAIN_HANDLE = 'rojas-main';

    /**
     * Registra los assets en los hooks de WordPress.
     */
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Encola el bundle principal (JS + CSS asociado).
     */
    public function enqueue_assets(): void
    {
        enqueue_asset(
            get_template_directory() . '/' . self::DIST_DIR,
            self::MAIN_ENTRY,
            [
                'handle' => self::MAIN_HANDLE,
                'css-media' => 'all',
                'in-footer' => true,
            ]
        );
    }
}
