<?php

/**
 * Funciones de utilidad global para el Rojas Stack.
 */

declare(strict_types=1);

if (!function_exists('rs_asset')) {
    /**
     * Helper para obtener la URL de un asset compilado por Vite.
     * 
     * @param string $path Ruta relativa al asset en resources/
     * @return string URL completa del asset
     */
    function rs_asset(string $path): string
    {
        return \RojasStack\Providers\AssetServiceProvider::get_asset_url($path);
    }
}
