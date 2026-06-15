<?php

namespace RojasStack\Providers;

/**
 * AssetServiceProvider
 * 
 * Gestiona la resolución de rutas de assets compilados por Vite.
 * Detecta si el servidor de desarrollo de Vite está activo o si debe
 * leer el archivo manifest.json en producción.
 */
class AssetServiceProvider
{
    private const VITE_SERVER = 'http://localhost:5173';
    private const DIST_DIR = 'dist';

    /**
     * Registra los assets en los hooks de WordPress.
     */
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Encola los scripts y estilos según el entorno.
     */
    public function enqueue_assets(): void
    {
        $is_dev = $this->is_dev();
        
        if ($is_dev) {
            // En desarrollo, inyectamos el cliente de Vite directamente
            wp_enqueue_script('vite-client', self::VITE_SERVER . '/@vite/client', [], null, true);
            wp_script_add_data('vite-client', 'type', 'module');

            // Cargamos el script principal (Vite se encarga del CSS)
            wp_enqueue_script('rojas-main', self::VITE_SERVER . '/resources/scripts/main.js', [], null, true);
            wp_script_add_data('rojas-main', 'type', 'module');
        } else {
            // En producción, leemos el manifiesto
            $manifest = $this->get_manifest();
            
            if (isset($manifest['resources/scripts/main.js'])) {
                $js_file = $manifest['resources/scripts/main.js']['file'];
                wp_enqueue_script('rojas-main', get_template_directory_uri() . '/' . self::DIST_DIR . '/' . $js_file, [], null, true);
                
                // Si el JS tiene CSS asociado, lo encolamos
                if (isset($manifest['resources/scripts/main.js']['css'])) {
                    foreach ($manifest['resources/scripts/main.js']['css'] as $css_file) {
                        wp_enqueue_style('rojas-main-css', get_template_directory_uri() . '/' . self::DIST_DIR . '/' . $css_file);
                    }
                }
            }
        }
    }

    /**
     * Detecta si estamos en entorno de desarrollo con Vite corriendo.
     */
    private function is_dev(): bool
    {
        // En DDEV, el archivo 'hot' suele estar en la raíz del tema si vite dev está activo
        return file_exists(get_template_directory() . '/hot');
    }

    /**
     * Lee el manifest.json.
     */
    private function get_manifest(): array
    {
        $manifest_path = get_template_directory() . '/' . self::DIST_DIR . '/.vite/manifest.json';
        
        if (!file_exists($manifest_path)) {
            return [];
        }

        return json_decode(file_get_contents($manifest_path), true) ?: [];
    }
}
