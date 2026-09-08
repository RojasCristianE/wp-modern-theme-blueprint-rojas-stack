# Arquitectura del Rojas Stack

> Documento de referencia (spec §10). Para la guía de agentes de IA, ver [`AGENTS.md`](../AGENTS.md).
> Para el catálogo de componentes y tokens, ver [`COMPONENTS.md`](./COMPONENTS.md).

## 1. Filosofía

**Monolito moderno, 100% code-first.** Todo el control reside en el código del tema: sin constructores visuales, sin plugins de UI, sin configuración en la base de datos salvo contenido y metadatos declarados en PHP.

Tres principios rectores:

1. **Separación de capas (responsabilidad única):** lógica en PHP (`app/`), vistas atómicas en Twig (`resources/views/`), configuración centralizada (`app/Config/features.php`).
2. **AI-First & predictibilidad:** tipado estricto (`declare(strict_types=1)` en todo `app/`), PHPStan nivel 6, docblocks con tipos en cada propiedad/método y auto-documentación in-situ. La estructura es predecible para humanos y agentes.
3. **Soberanía tecnológica:** herramientas OSS y estándares de industria (MariaDB, Valkey, Composer, Bun, Vite, Deployer).

## 2. Capas y responsabilidades

```text
┌─────────────────────────────────────────────────────────────┐
│  app/Config/features.php     ← Única fuente de verdad        │
│      (módulos on/off + parámetros)                           │
└──────────────────────────┬──────────────────────────────────┘
                           │ require
┌──────────────────────────▼──────────────────────────────────┐
│  app/Setup/ThemeSetup.php    ← Orquestador                   │
│  Registra core (siempre activo) + solo módulos habilitados   │
└──────┬───────────────┬───────────────┬──────────────────────┘
       │               │               │
┌──────▼─────┐  ┌──────▼─────┐  ┌──────▼──────────┐
│ Providers  │  │Controllers │  │ Fields          │
│ (hooks WP) │  │(orquestan  │  │ (Carbon Fields  │
│            │  │  datos→Twig│  │  code-first)    │
└──────┬─────┘  └──────┬─────┘  └─────────────────┘
       │               │
       │        ┌──────▼──────────────────┐
       │        │ resources/views/*.twig  │  ← Vistas atómicas
       │        └─────────────────────────┘
┌──────▼───────────────────────────────────────────────────────┐
│  WordPress (hooks, query, jerarquía de templates)             │
└───────────────────────────────────────────────────────────────┘
```

| Capa | Ubicación | Responsabilidad | Prohibido |
|---|---|---|---|
| Config | `app/Config/features.php` | Qué módulos están activos y sus parámetros | Contener lógica |
| Setup | `app/Setup/ThemeSetup.php` | Orquestar: registrar core + módulos habilitados | Conocer detalles de cada módulo |
| Providers | `app/Providers/` | Registrarse en hooks de WordPress | Renderizar HTML |
| Controllers | `app/Controllers/` | Preparar contexto y delegar a Twig | Registrar hooks ajenos a su módulo |
| Fields | `app/Fields/` | Declarar metadatos con Carbon Fields | Leer/escribir fuera de su dominio |
| Views | `resources/views/` | Presentación (Twig) | Consultar la DB directamente |

## 3. Flujo de una petición

```text
Request
  → functions.php (bootstrap: autoload, .env, Timber::init, Carbon Fields, ThemeSetup)
  → WordPress resuelve la query (¿existe en la DB?)
  → template_redirect:
      ├─ ¿Ruta virtual registrada? → RouteServiceProvider::dispatch_routes()
      │     ├─ serve_as_found(): status_header(200) + $wp_query->is_404 = false
      │     ├─ callback del módulo (BlogController, FormController, ...)
      │     └─ exit
      └─ No: jerarquía de templates de Timber
            index.php → Timber::render( jerarquía( 'index.twig' ) )
            (archive-{type}.twig, single-{type}.twig, page.twig, index.twig...)
  → Twig renderiza con el contexto de Timber
```

**Regla del stack (spec §12.1):** el router es para páginas **100% code-first** (blog, contacto, `/__components`); el contenido que vive en la DB usa la **jerarquía Twig** nativa. Conviven: la jerarquía es la base y el router es override explícito.

Como el router intercepta *después* de que WP resolvió la query, una ruta virtual nace marcada como 404. El dispatch la re-marca como found (200 + `is_404 = false`) para que el `<title>` y el meta robots sean correctos. Los 404 reales no se tocan.

## 4. Flujo de datos

### 4.1 Lectura (Timber / WP_Query)

- Los controllers usan `Timber::get_posts()` / `Timber::context()`, que envuelven `WP_Query` y entregan objetos listos para Twig.
- Para lecturas complejas específicas de un proyecto, usar `WP_Query`/`$wpdb` directamente o añadir Eloquent (Corcel) como dependencia local — nunca como parte del starter.

**Regla de oro:** la **escritura** de datos usa siempre funciones nativas de WordPress (`wp_insert_post`, `wp_update_post`) para respetar los hooks y la sanitización del core.

> Nota (2026-09-08): Corcel fue retirado del stack — sin uso en el código y con su dependencia `hautelook/phpass 0.3.4` yanked en Packagist (rompía instalaciones frescas). Ver ADR #11.

### 4.2 Metadatos (code-first)

Carbon Fields declara campos en clases de `app/Fields/`:

- `ThemeOptions` — opciones globales del tema (branding, redes). Se inyectan al contexto Twig vía el filtro `timber/context` en `ThemeSetup::add_to_context()` (`options.company_name`, `options.logo`, `options.social`).
- `PageFields` — campos de páginas (hero de portada), parte del módulo `pages`.

### 4.3 Escritura (formularios)

```text
Usuario envía (HTMX, hx-post)
  → admin-ajax.php  action=submit_{key}   con cabecera X-WP-Nonce
  → wp_ajax_submit_{key} → FormController::handle_submit()
      ├─ Validación Rakit (reglas declaradas en features.php)
      ├─ Falla  → re-renderiza form_template con errors + old
      └─ Ok     → wp_mail(recipient, subject con :name, body con todos los campos)
                → renderiza success_template (fragmento)
  → HTMX hace hx-swap sobre success.target
```

El nonce se inyecta globalmente: toda petición HTMX hacia la REST API/admin-ajax incluye `X-WP-Nonce` vía el evento `htmx:configRequest` (regla §2 del stack).

### 4.4 Lectura (búsqueda en vivo)

```text
Input del modal (HTMX, hx-get, delay 300ms)
  → admin-ajax.php  action=rs_live_search  (GET de solo lectura)
  → SearchController::render_fragment()
      ├─ término vacío → fragmento en estado inicial (sin tocar la DB)
      └─ término       → Timber::get_posts(s) → partials/search-results.twig
  → HTMX hace hx-swap en #search-results del modal
```

La búsqueda tradicional (`/?s=`) sigue la jerarquía de plantillas (`search.php` → `search.twig`) y renderiza la página completa: cada superficie tiene un único camino.

**Gestión de estado:** el estado persistente (filtros, paginación) vive en el servidor; **AlpineJS queda reservado para micro-interacciones efímeras** (dropdowns, modales).

## 5. Assets (Vite + vite-for-wp)

- **Producción:** `bun run build` genera `dist/manifest.json` (el plugin `v4wp` lo ubica en la raíz de `dist/`, no en `.vite/`). `AssetServiceProvider` encola el entrypoint con `Vite\enqueue_asset()` — un solo handle (`rojas-main`), `in-footer: true`; el CSS viaja dentro del bundle porque `main.js` importa `main.css`.
- **Desarrollo:** el plugin Vite `dev_server()` escribe `dist/vite-dev-server.json` mientras `bun run dev` corre. El lado PHP lo detecta y sirve `@vite/client` + los módulos desde el dev server con HMR. Al detenerse, el plugin limpia el archivo. La configuración vive en [`vite.config.js`](../vite.config.js).

> ⚠️ **Gotcha operativo:** si el dev server muere con `kill -9`/crash, `dist/vite-dev-server.json` queda huérfano y el sitio seguirá apuntando a URLs muertas hasta borrarlo a mano (`rm dist/vite-dev-server.json`).

## 6. Sistema de diseño

Tokens semánticos como **variables CSS en runtime**, puenteadas a Tailwind v4 con `@theme inline` (ver detalle en [`COMPONENTS.md`](./COMPONENTS.md)):

```css
@theme inline {
  --color-page: var(--rs-page);
  --color-surface: var(--rs-surface);
  /* ... */
}

:root { --rs-page: #f8fafc; /* light */ }

@media (prefers-color-scheme: dark) {
  :root { --rs-page: #0f172a; /* dark */ }
}
```

Las utilidades (`bg-page`, `bg-surface`, `text-ink`, `border-line`) resuelven la variable en runtime: el media query solo reasigna valores — **dark mode sin toggle manual y sin duplicar clases** (spec §8.2).

## 7. Calidad y testing

| Capa | Herramienta | Alcance |
|---|---|---|
| Estático | PHPStan nivel 6 | `app/` completo, con stubs de WP core + Carbon Fields |
| Estilo | Laravel Pint (PHP) · Biome (JS/CSS) | Formato + lint |
| Unit | Pest + Brain Monkey + Patchwork | `tests/Unit/` — sin WordPress real: Brain Monkey stuba funciones WP; Patchwork redefine clases (`Timber\Timber::*`) y métodos internos |
| E2E | Playwright (Chrome del sistema) | `e2e/` — contra el sitio real de DDEV; `PLAYWRIGHT_BASE_URL` sobre-escribible para staging |
| Pre-commit | Lefthook | biome + phpstan + pest por archivos staged |
| CI | GitHub Actions | biome + build Vite · PHPStan + Pest |

**Patrón de tests:** los tests de controllers ejecutan la lógica real y capturan lo que sale por los bordes (`Timber::render` redefinido con Patchwork, `wp_mail` esperado con Brain Monkey). La librería `kucrut/vite-for-wp` se testea **en integración** (corre de verdad contra un manifest fixture; Patchwork no puede redefinirla porque el autoload la carga antes del bootstrap).

## 8. Despliegue (theme-only)

- El repositorio contiene **solo el tema**: el core de WordPress mantiene sus actualizaciones automáticas de seguridad intactas en producción.
- **Deployer** gestiona releases atómicas vía symlinks (`releases/` → `current`), con `shared/` para `wp-content/uploads` y `.env` (los datos persistentes sobreviven a cada deploy).
- Pipeline: GitHub Actions (lint → test → Deployer). Versionado automático con Semantic Release sobre Conventional Commits.
- El servidor puede estar orquestado externamente (Kamal/Traefik/Caddy); Caddy debe servir el enrutamiento WP: `try_files {path} {path}/ /index.php?{query}`.
- La receta de Deployer (`deploy.php`) y la configuración de servidor viven en `infra/` (se completan en el paso 11 de la spec).

## 9. Decisiones registradas (ADRs breves)

| # | Decisión | Razón |
|---|---|---|
| 1 | Twig (Timber) en vez de PHP templates | Separación estricta lógica/vista; sintaxis segura para IA |
| 2 | `features.php` como única fuente de verdad | Un módulo deshabilitado no registra rutas, hooks ni campos |
| 3 | Router genérico (`add_route`) sin conocimiento de módulos | Los módulos registran lo suyo; el router no crece con cada feature |
| 4 | `kucrut/vite-for-wp` sobre enqueue manual | Manifest estándar, detección de dev server mantenida por la librería |
| 5 | Jerarquía Twig como base + router como override | Contenido de DB resuelve nativo; rutas code-first explícitas (§12.1) |
| 6 | Escritura nativa de WP; lectura vía Timber/WP_Query | Respetar hooks/sanitización del core de WP |
| 7 | HTMX para estado, Alpine para efímero | Sin SPAs; estado persistente verificable en servidor |
| 8 | Tokens CSS runtime + `@theme inline` | Dark mode sin toggle ni duplicación de clases |
| 9 | Tests con Brain Monkey + Patchwork | Suite rápida sin instalar WordPress real |
| 10 | Wildcards de dependencias (fase experimental) | `composer.lock`/`bun.lock` son el mecanismo real de reproducibilidad |
| 11 | Corcel retirado del stack; rangos caret anclados (2026-09-08) | Corcel sin uso en el código y con `hautelook/phpass 0.3.4` yanked en Packagist; los rangos caret fijan techos de major explícitos |
