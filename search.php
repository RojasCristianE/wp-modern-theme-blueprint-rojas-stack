<?php
/**
 * Controlador de la búsqueda tradicional (jerarquía de plantillas).
 *
 * Renderiza la página completa de resultados (/?s=…). El fragmento en
 * vivo del modal NO pasa por aquí: HTMX solicita el endpoint dedicado
 * admin-ajax?action=rs_live_search (SearchController), que responde
 * SOLO partials/search-results.twig — un solo camino por contrato.
 *
 * @package RojasStack
 */

$context                 = Timber\Timber::context();
$context['posts']        = Timber\Timber::get_posts();
$context['search_query'] = get_search_query();

Timber\Timber::render(['search.twig', 'archive.twig', 'index.twig'], $context);
