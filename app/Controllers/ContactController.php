<?php

namespace RojasStack\Controllers;

use Rakit\Validation\Validator;
use Timber\Timber;

/**
 * ContactController
 * 
 * Gestiona el envío de formularios de contacto usando HTMX y Rakit
 * (módulo 'forms' de features.php). También sirve el fragmento del
 * formulario que HTMX refresca vía admin-ajax.
 */
class ContactController
{
    /**
     * Config del módulo 'forms' (app/Config/features.php).
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function register(): void
    {
        // Envío del formulario (sin login y con login)
        add_action('wp_ajax_submit_contact', [$this, 'handle_submit']);
        add_action('wp_ajax_nopriv_submit_contact', [$this, 'handle_submit']);

        // Refresco del fragmento del formulario vía HTMX
        add_action('wp_ajax_get_contact_form', [$this, 'render_form_fragment']);
        add_action('wp_ajax_nopriv_get_contact_form', [$this, 'render_form_fragment']);
    }

    /**
     * Renderiza la página de contacto (Ruta Virtual)
     */
    public function render_page(): void
    {
        $context = Timber::context();
        $context['title'] = __('Contáctanos', 'rojas-stack');
        
        Timber::render('contact.twig', $context);
    }

    /**
     * Devuelve el fragmento del formulario para el hx-swap de HTMX.
     */
    public function render_form_fragment(): void
    {
        Timber::render('partials/contact-form.twig', Timber::context());
        wp_die();
    }

    public function handle_submit(): void
    {
        // 1. Validar los datos con Rakit
        $validator = new Validator;
        $validation = $validator->make($_POST, [
            'name'    => 'required|min:3',
            'email'   => 'required|email',
            'message' => 'required|min:10',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            // Si falla, devolvemos el formulario con errores
            $context = Timber::context();
            $context['errors'] = $validation->errors()->firstOfAll();
            $context['old']    = $_POST;
            
            Timber::render('partials/contact-form.twig', $context);
            wp_die();
        }

        // 2. Si es válido, "enviar" el correo (capturado por Mailpit)
        $data = $validation->getValidData();
        
        $to      = $this->resolve_recipient();
        $subject = 'Nuevo mensaje de: ' . $data['name'];
        $body    = "Has recibido un nuevo mensaje desde el Rojas Stack Blueprint:\n\n";
        $body   .= "Nombre: {$data['name']}\n";
        $body   .= "Email: {$data['email']}\n";
        $body   .= "Mensaje: {$data['message']}\n";

        wp_mail($to, $subject, $body);

        // 3. Devolver fragmento de éxito (usa el componente alert vía HTMX)
        Timber::render('partials/contact-success.twig', Timber::context());
        
        wp_die();
    }

    /**
     * Resuelve el destinatario según features.php: 'admin_email' usa la
     * opción de WP; cualquier otro valor se interpreta como email directo.
     */
    private function resolve_recipient(): string
    {
        $recipient = $this->config['contact_recipient'] ?? 'admin_email';

        if (\is_string($recipient) && $recipient !== 'admin_email' && $recipient !== '') {
            return $recipient;
        }

        return (string) get_option('admin_email');
    }
}
