<?php
/**
 * Fallback obligatorio de WordPress.
 * En el Rojas Stack, este archivo actúa como un controlador de última instancia.
 * 
 * @package RojasStack
 */

$context = Timber\Timber::context();

// DEBUG: Forzar renderizado
Timber\Timber::render('index.twig', $context);
