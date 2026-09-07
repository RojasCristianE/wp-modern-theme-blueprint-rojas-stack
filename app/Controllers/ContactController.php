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

        // 3. Devolver mensaje de éxito
        echo '<div class="p-6 bg-emerald-50 border border-emerald-200 rounded-2xl text-center shadow-sm">
                <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-white mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-emerald-900 font-bold text-lg mb-1">¡Mensaje Enviado!</h3>
                <p class="text-emerald-700 text-sm">Tu mensaje ha sido capturado por Mailpit con éxito.</p>
                <button hx-get="' . get_bloginfo('url') . '/wp-admin/admin-ajax.php?action=get_contact_form" hx-target="#contact-form-wrapper" class="mt-4 text-xs font-bold text-emerald-600 hover:underline underline-offset-4">Enviar otro mensaje</button>
              </div>';
        
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
