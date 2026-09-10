# Ending Responsive Images — Edición Revisada y Adaptada a WordPress

**Autor original:** Jason Grigsby  
**Publicado originalmente:** 11 de junio de 2026  
**Fuente:** [Cloud Four](https://cloudfour.com/thinks/ending-responsive-images)  
**Adaptación e Implementación:** Arquitectura de Caché Efímera, Docker (imgproxy), PHP Directo y Colas para WordPress en Medios Digitales.

---

## Parte 1: El Artículo Original

Mat Marquis, ex presidente del [Responsive Images Working Group](https://www.w3.org/community/respimg/) (RICG) y [boxeador aficionado](https://www.indiegogo.com/en/projects/matmarquis/wilto-s-first-fight-night), no es conocido por guardarse los golpes. Y sin embargo, Marquis se quedó corto cuando declaró [El fin de las imágenes responsivas](https://piccalil.li/blog/the-end-of-responsive-images/). Mat dejó las imágenes responsivas contra las cuerdas. Mi intención es noquearlas por completo.

O más bien, noquear la mayoría de las imágenes responsivas: las que están debajo del pliegue (*below the fold*). Permíteme explicarlo.

### La condición de carrera de las imágenes responsivas

Al inicio de la búsqueda de una solución para imágenes responsivas, el reto parecía sencillo: el elemento `img` solo admitía un archivo de origen y necesitábamos múltiples fuentes para adaptarnos al diseño. Sin embargo, si proveemos múltiples fuentes, ¿cómo sabe el navegador cuál elegir?

Ahí radicaba el dilema. Los navegadores descargan las imágenes **antes** de calcular la diagramación (layout CSS) de la página. Y si el navegador no conoce el diseño, no sabe qué tamaño ocupará la imagen en pantalla. Como escribí en [2012](https://cloudfour.com/thinks/the-real-conflict-behind-picture-and-srcset/):

> ¿Cómo reconciliamos un analizador preliminar (*pre-parser*) que quiere saber qué tamaño de imagen descargar con anticipación, con una técnica de diseño que responde a su entorno una vez calculada la estructura CSS?

Resolver este dilema fue lo que dio origen a `srcset` y `sizes`. Uno le indica al navegador qué opciones de archivos tiene disponibles, y el otro le da la información necesaria para calcular el tamaño en base a una medida que siempre conoce: el ancho del viewport.

### El Lazy Loading lo cambió todo

Con los años, la carga diferida (*lazy loading*) se convirtió en práctica común. Podemos posponer la carga de imágenes fuera de la pantalla inicial con total seguridad para concentrar los recursos del navegador en los activos visibles inmediatamente.

Tantos desarrolladores lo adoptaron que se convirtió en estándar web. Añadir `loading="lazy"` a cualquier imagen instruye al navegador a demorar su descarga hasta que esté a punto de entrar al viewport.

Al no descargarse de inmediato, la condición de carrera ya no existe. Cuando la imagen lazy-loaded se descarga, el navegador **ya conoce su tamaño exacto en píxeles**.

Esto permite simplificar la sintaxis de `sizes`, tal como explicó Mat Marquis:

> Hace pocas semanas aterrizaron dos parches en [Gecko](https://bugzilla.mozilla.org/show_bug.cgi?id=1819581) y [WebKit](https://bugs.webkit.org/show_bug.cgi?id=310025)... alineando Gecko y WebKit con [Blink](https://issues.chromium.org/issues/40862170#comment27) en el soporte para el valor `auto` en el atributo `sizes`. Tamaños automáticos: el navegador determina el tamaño renderizado junto con los demás factores. Solo suministra la lista en `srcset`, agrega `sizes="auto"` y deja que el navegador haga el resto.

Siempre que una imagen tenga `loading="lazy"`, se puede utilizar `sizes="auto"`:

```html
<img loading="lazy" src="cat.jpeg" srcset="cat-650.jpeg 650w, cat-960.jpeg 960w, cat-1400.jpeg 1400w" sizes="auto, (min-width: 1040px) 650px, calc(94.44vw - 15px)" width="650" height="400" alt="Mi gato Boyle">
```

> **Advertencia crítica:** [Las imágenes sobre el pliegue inicial (*above the fold*) NUNCA deben llevar lazy loading](https://cloudfour.com/thinks/stop-lazy-loading-product-and-hero-images/). Suelen ser tu imagen [Largest Contentful Paint (LCP)](https://web.dev/articles/lcp) en [Core Web Vitals](https://web.dev/explore/learn-core-web-vitals) y deben descargarse lo antes posible mediante la sintaxis clásica de `srcset` + `sizes`.

### Eliminando `srcset` con Client Hints

Simplificar `sizes` es solo el inicio. Con **Responsive Image Client Hints**, el navegador puede solicitar al servidor el ancho exacto mediante cabeceras HTTP:

```http
Accept-CH: Sec-CH-Width
Permissions-Policy: ch-width=(self "https://images.cloudfour.com")
```

Y el navegador envía:

```http
GET cat.jpg
Accept: image/webp,image/*,*/*;q=0.8
Sec-CH-Width: 650
```

Así, el HTML se reduce a su mínima expresión:

```html
<img loading="lazy" src="cat.jpeg" width="650" height="400" sizes="auto" alt="Mi gato Boyle">
```

---

## Parte 2: Adaptación para WordPress en Medios de Comunicación

En un medio digital o portal de noticias, los artículos tienen una **vida útil corta (horas o pocos días)** y una curva de tráfico con picos muy pronunciados.

Guardar físicamente 6 u 8 tamaños recortados para siempre por cada imagen satura el almacenamiento del servidor con miles de archivos huérfanos que nadie volverá a consultar.

### El Flujo de Caché Jerárquica de 3 Capas

```
[ Navegador del Usuario ]
       │ (1. Caché local: 1 año / immutable)
       ▼
 [ CDN: Cloudflare Edge ]
       │ (2. Edge Cache: 30 días a 1 año)
       ▼
[ Servidor de Origen (Nginx) ]
       │ (3. Caché de Disco Local: TTL 30 días)
       ▼ (Si no existe el recorte)
[ Motor de Redimensionamiento (Docker o PHP) ]
       │ (4. Lee el Original y genera el tamaño pedido)
       ▼
[ Almacenamiento Permanente ]
  (Solo guarda la imagen original en alta resolución)
```

1. **Capa Navegador:** `Cache-Control: public, max-age=31536000, immutable`. Cero peticiones repetidas por el mismo lector.
2. **Capa CDN (Cloudflare):** Cachea cada tamaño derivado (`?w=800` o `/w:800/`) en los servidores perimetrales. Absorbe más del **98%** de las peticiones concurrentes.
3. **Capa Origen con Evicción Automática:** Los recortes temporales residen en `/wp-content/cache/images/`. Un cron job diario purga los que no han sido leídos en 30 días:
   ```bash
   # Tarea nocturna en crontab
   0 3 * * * find /var/www/html/wp-content/cache/images -type f -atime +30 -delete
   ```
4. **Resurrección Transparente:** Si una noticia de hace 3 años se vuelve a viralizar, Cloudflare consulta al origen, el motor regenera el recorte en milisegundos y la caché vuelve a calentarse automáticamente.

---

## Parte 3: Enfoque con Docker (imgproxy) — Recomendado para Producción

Este enfoque descarga a PHP de cualquier procesamiento gráfico y delega la transformación a un microservicio en Go con la librería ultra-rápida `libvips`.

### 1. `docker-compose.yml` para `imgproxy`

```yaml
services:
  imgproxy:
    image: darthsim/imgproxy:latest
    container_name: wp-imgproxy
    restart: unless-stopped
    environment:
      - IMGPROXY_BIND=:8080
      - IMGPROXY_READ_TIMEOUT=10
      - IMGPROXY_WRITE_TIMEOUT=10
      - IMGPROXY_DOWNLOAD_TIMEOUT=10
      - IMGPROXY_MAX_SRC_RESOLUTION=50
      - IMGPROXY_QUALITY=82
      - IMGPROXY_AVIF_QUALITY=75
      - IMGPROXY_AUTO_WEBP=true
      - IMGPROXY_AUTO_AVIF=true
      - IMGPROXY_LOCAL_FILESYSTEM_ROOT=/var/www/uploads
    volumes:
      - /var/www/html/wp-content/uploads:/var/www/uploads:ro
    ports:
      - "127.0.0.1:8085:8080"
```

### 2. Configuración de Nginx (Reverse Proxy + Caché Estática en Origen)

```nginx
# Zona de caché en memoria/disco de Nginx
proxy_cache_path /var/www/cache/imgproxy levels=1:2 keys_zone=img_cache:20m max_size=10g inactive=30d use_temp_path=off;

server {
    server_name tudominio.com;
    root /var/www/html;

    # Endpoint optimizado para imágenes redimensionadas
    location ~* ^/cdn-cgi/image/w:([0-9]+)/(.*)$ {
        set $width $1;
        set $image_path $2;

        proxy_cache img_cache;
        proxy_cache_valid 200 30d;
        proxy_cache_use_stale error timeout updating http_500 http_502 http_503;
        proxy_cache_lock on; # Previene estampidas de caché

        proxy_pass http://127.0.0.1:8085/unsafe/rs:fill:$width:0:0/plain/local:///$image_path;
        proxy_set_header Host $host;

        add_header X-Cache-Status $upstream_cache_status;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }
}
```

### 3. Filtro en WordPress (`functions.php`)

```php
add_filter('wp_content_img_tag', function($filtered_image, $context, $attachment_id) {
    if (strpos($filtered_image, 'loading="lazy"') === false) {
        return $filtered_image; // Respeta el hero/LCP
    }

    // Inyecta sizes="auto"
    $filtered_image = preg_replace('/sizes="([^"]+)"/', 'sizes="auto, $1"', $filtered_image);

    // Reemplaza el src para apuntar al proxy dinámico con fallback de 800px
    $upload_dir = wp_upload_dir();
    $image_src = wp_get_attachment_url($attachment_id);
    $relative_path = str_replace($upload_dir['baseurl'] . '/', '', $image_src);

    $dynamic_url = home_url("/cdn-cgi/image/w:800/{$relative_path}");
    $filtered_image = preg_replace('/src="([^"]+)"/', 'src="' . esc_url($dynamic_url) . '"', $filtered_image);

    return $filtered_image;
}, 10, 3);
```

---

## Parte 4: Enfoque Directo (PHP / WordPress Puro sin Contenedores)

Si tu entorno es un hosting administrado o sin soporte para Docker, puedes implementar el escalado en PHP aplicando tres salvaguardas obligatorias para evitar caídas del servidor:

1. **Cuantización de Anchos (Bucketing):** Redondear las peticiones a saltos de 150px o 200px (`320, 480, 640, 800, 1024, 1280`) para evitar miles de variantes microscópicas.
2. **Cerrojo contra Estampidas (`flock`):** Solo un proceso PHP genera la imagen; los demás concurrentes esperan a que el archivo exista.
3. **Descarga Directa por Nginx (`try_files`):** Una vez generado, Nginx sirve el `.webp` directamente sin volver a tocar PHP.

### Código de Ejemplo: Manejador de Caché en PHP

```php
// Endpoint en /wp-content/plugins/mi-optimizador/resize.php
$requested_w = (int)($_GET['w'] ?? 800);
$buckets = [320, 480, 640, 800, 1024, 1280, 1600];

// Encontrar el escalón más cercano superior
$target_w = 800;
foreach ($buckets as $b) {
    if ($b >= $requested_w) {
        $target_w = $b;
        break;
    }
}

$file_rel = sanitize_text_field($_GET['file']);
$original = WP_CONTENT_DIR . '/uploads/' . $file_rel;
$cache_path = WP_CONTENT_DIR . "/cache/images/w_{$target_w}/" . $file_rel;

if (!file_exists($cache_path)) {
    @mkdir(dirname($cache_path), 0755, true);
    $lock_file = $cache_path . '.lock';
    $fp = fopen($lock_file, 'w+');

    if (flock($fp, LOCK_EX)) {
        if (!file_exists($cache_path)) { // Doble comprobación
            $editor = wp_get_image_editor($original);
            if (!is_wp_error($editor)) {
                $editor->resize($target_w, null);
                $editor->set_quality(82);
                $editor->save($cache_path, 'image/webp');
            }
        }
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    @unlink($lock_file);
}

// Entregar archivo
header('Content-Type: image/webp');
header('Cache-Control: public, max-age=31536000, immutable');
readfile($cache_path);
exit;
```

---

## Parte 5: Administrador de Colas y Procesamiento Asíncrono

¿Cuándo es necesario implementar una cola de mensajería (Redis / Valkey / RabbitMQ / Action Scheduler)?

| Operación | ¿Requiere Cola? | Justificación |
|---|---|---|
| **Lectura bajo demanda (On-demand reader)** | **NO** | Con Cloudflare + cerrojos (`locks`) en servidor, solo la primera visita compila el tamaño. La cola introduciría latencia visual (*placeholder* en blanco para el primer lector). |
| **Publicación de Noticia Crítica (Breaking News)** | **SÍ (Recomendado)** | Cuando la redacción publica una noticia urgente que enviará alertas *push* a millones de personas a la vez. |
| **Regeneración o Migración de Mediateca** | **SÍ (Obligatorio)** | Tareas por lotes que no deben bloquear las peticiones HTTP del CMS. |

### Flujo de Pre-Calentamiento Asíncrono (Warm-up en Segundo Plano)

```
[ Redactor publica noticia ]
            │
            ▼ (Hook: `publish_post`)
[ Cola Redis / Action Scheduler ]
            │
            ▼ (Worker en segundo plano CLI)
[ Pre-genera los 4 tamaños principales ]
            │
            ▼
[ Calienta la caché de Cloudflare vía HTTP GET ]
            │
            ▼
[ Usuarios reciben la alerta push ] ──> ¡Todo servido desde Cloudflare en 5ms!
```

### Implementación con Action Scheduler en WordPress:

```php
// Al publicar un artículo, encola la tarea en segundo plano
add_action('publish_post', function($post_id) {
    if (has_post_thumbnail($post_id)) {
        $thumb_id = get_post_thumbnail_id($post_id);
        as_enqueue_async_action('warmup_responsive_images', ['attachment_id' => $thumb_id]);
    }
});

// El worker procesa la cola sin bloquear al redactor ni a los visitantes
add_action('warmup_responsive_images', function($attachment_id) {
    $buckets = [480, 800, 1200];
    $upload_dir = wp_upload_dir();
    $file = str_replace($upload_dir['baseurl'] . '/', '', wp_get_attachment_url($attachment_id));

    foreach ($buckets as $w) {
        // Ejecuta la petición interna para generar y calentar el recorte
        $warmup_url = home_url("/cdn-cgi/image/w:{$w}/{$file}");
        wp_remote_get($warmup_url, ['timeout' => 5, 'sslverify' => false]);
    }
});
```

---

## Resumen Comparativo de Estrategias

| Criterio | Estándar WP Actual | Docker (`imgproxy`) | Directo PHP Cuantizado |
|---|---|---|---|
| **Espacio en Disco** | Se satura (almacena todo para siempre) | Óptimo (Caché efímera de 30 días) | Óptimo (Caché efímera de 30 días) |
| **Carga de CPU en el Servidor** | En subida masiva inicial | Mínima (Go + `libvips`) | Media (PHP Imagick con cerrojos) |
| **Rendimiento Lector** | Bueno | Excelente (WebP/AVIF al vuelo) | Excelente (tras primer impacto) |
| **Complejidad de Configuración** | Cero | Media (Docker + Nginx) | Baja (Filtros PHP + Nginx) |
| **Integración con Cloudflare** | Parcial | 100% nativa con Edge Caching | 100% nativa con Edge Caching |
