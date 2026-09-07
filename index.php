<?php

/**
 * Fallback obligatorio de WordPress.
 *
 * En el Rojas Stack este archivo actúa como punto de entrada de la
 * jerarquía de templates de WordPress. Timber resuelve el .twig según
 * la petición (home, archive-{post_type}, single-{post_type}, ...):
 * https://timber.github.io/docs/guides/template-hierarchy/
 *
 * @package RojasStack
 */

declare(strict_types=1);

$templates = ['index.twig'];

if (is_singular()) {
    $post    = Timber\Timber::get_post();
    $context = Timber\Timber::context();
    $context['post'] = $post;

    Timber\Timber::render([
        'single-' . $post->post_type . '.twig',
        'single.twig',
    ], $context);

    return;
}

if (is_archive()) {
    $context = Timber\Timber::context();

    $post_type = get_query_var('post_type');
    $templates = array_merge([$post_type ? 'archive-' . $post_type . '.twig' : 'archive.twig'], $templates);

    Timber\Timber::render($templates, $context);

    return;
}

// Home/front page y cualquier otro caso: index.twig
$context = Timber\Timber::context();
$context['post'] = Timber\Timber::get_post();

Timber\Timber::render($templates, $context);
