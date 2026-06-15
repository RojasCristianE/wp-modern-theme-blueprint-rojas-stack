<?php
/**
 * Fallback obligatorio de WordPress.
 * En el Rojas Stack, este archivo actúa como un controlador de última instancia.
 * 
 * @package RojasStack
 */

$context = Timber\Timber::context();

// Renderizamos la plantilla index.twig por defecto en el fallback
Timber\Timber::render('index.twig', $context);
