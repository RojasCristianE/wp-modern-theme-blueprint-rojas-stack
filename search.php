<?php
/**
 * Controlador de búsqueda dinámico.
 * Maneja tanto la búsqueda tradicional como las peticiones HTMX para resultados en tiempo real.
 * 
 * @package RojasStack
 */

$context = Timber\Timber::context();
$search_query = get_search_query();

$context['posts'] = Timber\Timber::get_posts();
$context['search_query'] = $search_query;

// Detectar si la petición viene de HTMX
$is_htmx = isset($_SERVER['HTTP_HX_REQUEST']);

if ($is_htmx) {
    // Si es HTMX, renderizamos solo el fragmento de resultados
    Timber\Timber::render('partials/search-results.twig', $context);
} else {
    // Si es una carga normal, renderizamos la página completa
    Timber\Timber::render(['search.twig', 'archive.twig', 'index.twig'], $context);
}
