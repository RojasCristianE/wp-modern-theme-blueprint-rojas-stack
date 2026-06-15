<?php
/**
 * El controlador para mostrar un post individual.
 * 
 * @package RojasStack
 */

$context         = Timber\Timber::context();
$timber_post     = Timber\Timber::get_post();
$context['post'] = $timber_post;

if (post_password_required($timber_post->ID)) {
    Timber\Timber::render('single-password.twig', $context);
} else {
    Timber\Timber::render(['single-' . $timber_post->ID . '.twig', 'single-' . $timber_post->post_type . '.twig', 'single.twig'], $context);
}
