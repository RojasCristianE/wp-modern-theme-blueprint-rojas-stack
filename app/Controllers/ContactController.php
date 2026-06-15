<?php

namespace RojasStack\Controllers;

use Rakit\Validation\Validator;
use Timber\Timber;

/**
 * ContactController
 * 
 * Gestiona el envío de formularios de contacto usando HTMX y Rakit.
 */
class ContactController
{
    public function register(): void
    {
        // Usamos el hook de WordPress para AJAX (sin login y con login)
        add_action('wp_ajax_submit_contact', [$this, 'handle_submit']);
        add_action('wp_ajax_nopriv_submit_contact', [$this, 'handle_submit']);
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
        
        $to      = get_option('admin_email');
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
}
