<?php

namespace RojasStack\Controllers;

use Rakit\Validation\Validator;
use Timber\Timber;

/**
 * FormController
 *
 * Motor de formularios genérico (spec paso 7): cada formulario definido
 * en app/Config/features.php (módulo 'forms' → clave 'forms') se instancia
 * con su configuración y obtiene:
 *
 *   - Ruta virtual de página (registrada por ThemeSetup vía add_route()).
 *   - Hooks AJAX derivados de la clave: submit_{key} y get_form_{key}.
 *   - Validación Rakit con las reglas declaradas en 'fields'.
 *   - Envío de correo al destinatario configurado con el body generado
 *     a partir de los campos.
 *   - Fragmento de éxito renderizado desde 'success_template'.
 *
 * El formulario de contacto es la primera instancia ('contact'); añadir
 * otro formulario es solo una entrada nueva en features.php.
 */
class FormController
{
    /**
     * Config del formulario (features.php → forms.forms.{key}).
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * Clave del formulario (deriva los nombres de hooks AJAX).
     */
    private string $key;

    /**
     * @param string               $key    Clave del formulario (ej. 'contact')
     * @param array<string, mixed> $config Configuración del formulario
     */
    public function __construct(string $key, array $config = [])
    {
        $this->key    = $key;
        $this->config = $config;
    }

    public function register(): void
    {
        // Envío del formulario (sin login y con login)
        add_action('wp_ajax_submit_' . $this->key, [$this, 'handle_submit']);
        add_action('wp_ajax_nopriv_submit_' . $this->key, [$this, 'handle_submit']);

        // Refresco del fragmento del formulario vía HTMX
        add_action('wp_ajax_get_form_' . $this->key, [$this, 'render_fragment']);
        add_action('wp_ajax_nopriv_get_form_' . $this->key, [$this, 'render_fragment']);
    }

    /**
     * Renderiza la página del formulario (Ruta Virtual).
     */
    public function render_page(): void
    {
        $context          = Timber::context();
        $context['title'] = $this->config['title'] ?? ucfirst($this->key);

        Timber::render((string) $this->config['page_template'], $context);
    }

    /**
     * Devuelve el fragmento del formulario para el hx-swap de HTMX.
     */
    public function render_fragment(): void
    {
        Timber::render((string) $this->config['form_template'], Timber::context());
        wp_die();
    }

    public function handle_submit(): void
    {
        // 1. Validar los datos con Rakit según las reglas declaradas
        $validator  = new Validator;
        $validation = $validator->make($_POST, $this->config['fields'] ?? []);

        $validation->validate();

        if ($validation->fails()) {
            // Si falla, devolvemos el formulario con errores
            $context           = Timber::context();
            $context['errors'] = $validation->errors()->firstOfAll();
            $context['old']    = $_POST;

            Timber::render((string) $this->config['form_template'], $context);
            wp_die();
        }

        // 2. Si es válido, enviar el correo (capturado por Mailpit en dev)
        $data = $validation->getValidData();

        $this->send($data);

        // 3. Devolver fragmento de éxito (usa el componente alert vía HTMX)
        $context           = Timber::context();
        $context['success'] = [
            'title'        => $this->config['success']['title'] ?? __('¡Mensaje Enviado!', 'rojas-stack'),
            'message'      => $this->config['success']['message'] ?? '',
            'fragment_url' => get_bloginfo('url') . '/wp-admin/admin-ajax.php?action=get_form_' . $this->key,
            'target'       => $this->config['success']['target'] ?? '#contact-form-wrapper',
        ];

        Timber::render((string) $this->config['success_template'], $context);

        wp_die();
    }

    /**
     * Compone y envía el correo con todos los campos declarados.
     *
     * @param array<string, mixed> $data Datos validados
     */
    private function send(array $data): void
    {
        $body = "Has recibido un nuevo mensaje desde el Rojas Stack Blueprint:\n\n";

        foreach (array_keys($this->config['fields'] ?? []) as $field) {
            $label = ucfirst(str_replace('_', ' ', (string) $field));
            $body .= "{$label}: " . ($data[$field] ?? '') . "\n";
        }

        wp_mail($this->resolve_recipient(), $this->resolve_subject($data), $body);
    }

    /**
     * Resuelve el destinatario: 'admin_email' usa la opción de WP;
     * cualquier otro valor se interpreta como email directo.
     */
    private function resolve_recipient(): string
    {
        $recipient = $this->config['recipient'] ?? 'admin_email';

        if (\is_string($recipient) && $recipient !== 'admin_email' && $recipient !== '') {
            return $recipient;
        }

        return (string) get_option('admin_email');
    }

    /**
     * Resuelve el asunto, reemplazando el token ':name' si existe.
     *
     * @param array<string, mixed> $data
     */
    private function resolve_subject(array $data): string
    {
        $subject = $this->config['subject']
            ?? sprintf(__('Nuevo mensaje por el formulario: %s', 'rojas-stack'), ucfirst($this->key));

        if (str_contains($subject, ':name')) {
            $subject = str_replace(':name', (string) ($data['name'] ?? ''), $subject);
        }

        return $subject;
    }
}
