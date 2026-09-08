# Especificación del Rojas Starter Theme

> **Nombre corto del requerimiento:** rojas-starter  
> **Estado:** Borrador — lista para refinamiento y ejecución  
> **Audiencia:** Comunidad open source de desarrolladores WordPress  
> **Modelo de reutilización:** Starter theme (copiar, personalizar y desplegar por proyecto)  
> **Stack base:** PHP 8.5+, Timber/Twig, Carbon Fields, Vite, Tailwind CSS v4, HTMX, AlpineJS

---

## 1. Propósito y alcance

### 1.1 Propósito
Transformar el **Rojas Stack Blueprint** actual en un **starter theme WordPress** moderno, flexible y documentado que sirva como base para proyectos de blogs y sitios de contenido, sin depender del editor de bloques ni de constructores visuales.

### 1.2 Alcance
- Tema monolítico WordPress 100% code-first.
- Sin soporte de Gutenberg/Block Editor: todo se controla desde PHP/Twig.
- Flexible por diseño: casi todo debe poder activarse/desactivarse o reemplazarse.
- Distribuido como template de GitHub y/o paquete Packagist, no por WordPress.org.

### 1.3 No está dentro del alcance
- Distribución en WordPress.org.
- Soporte oficial a WooCommerce u otros plugins en la versión inicial.
- Backwards compatibility con PHP < 8.5.
- Páginas construidas con bloques o Full Site Editing.

---

## 2. Objetivos y no-objetivos

### 2.1 Objetivos
- [ ] Proveer una base de código limpia, tipada y predecible para nuevos proyectos.
- [ ] Separar claramente lógica (PHP), presentación (Twig) y configuración.
- [ ] Ofrecer un sistema de módulos centralizado y configurable.
- [ ] Incluir un sistema de diseño completo con tokens, layout responsivo y dark mode.
- [ ] Entregar una experiencia de desarrollo moderna con HMR, linting, tests E2E, tests unitarios y git hooks.
- [ ] Facilitar la adopción por agentes de IA mediante documentación estructurada (`AGENTS.md`).

### 2.2 No-objetivos
- No es un parent theme con child-theme support forzado.
- No incluye integraciones con plugins de terceros.
- No busca compatibilidad multisitio o multisite específica en la versión inicial.

---

## 3. Audiencia objetivo

1. **Desarrolladores WordPress** que prefieren PHP moderno y Twig antes que bloques.
2. **Agencias/freelancers** que necesitan arrancar proyectos rápidamente con una base sólida.
3. **Equipos que usan agentes de IA** para acelerar el desarrollo.
4. **Comunidad open source** interesada en un starter theme con opiniones modernas.

---

## 4. Decisiones de arquitectura

### 4.1 Patrón MVC ligero
- **Modelos:** lectura vía `Timber::get_posts()`/`WP_Query` (Eloquent/Corcel opcional por proyecto, no incluido).
- **Vistas:** Twig templates bajo `resources/views/`.
- **Controladores:** PHP clases en `app/Controllers/` que orquestan datos para Twig.

### 4.2 Service Providers
Cada funcionalidad mayor se registra a través de un provider, inyectado por `ThemeSetup.php`:

- `AssetServiceProvider` → carga de assets con Vite.
- `MenuServiceProvider` → menús de navegación programáticos.
- `RouteServiceProvider` → rutas virtuales (páginas sin existir en DB).
- `SeoServiceProvider` → schemas JSON-LD con Spatie.
- `FormServiceProvider` → formularios HTMX (evolución de `ContactController`).

### 4.3 Estado e interacción
- **Estado persistente:** en el servidor (rutas, paginación, filtros).
- **Micro-interacciones:** AlpineJS para dropdowns, modales, toggles.
- **Actualizaciones parciales:** HTMX para formularios y búsquedas sin recargar.

### 4.4 Metadatos code-first
- Carbon Fields para opciones del tema y campos de páginas/post.
- Definidos en clases bajo `app/Fields/`.

### 4.5 Assets modernos
- Vite como bundler principal.
- Tailwind CSS v4 con PostCSS.
- Integración con WordPress a través de `kucrut/vite-for-wp` (PHP + plugin Vite).

---

## 5. Módulos y funcionalidades

Todos los módulos se activan/desactivan desde un archivo de configuración central `app/Config/features.php`.

| Módulo | Estado por defecto | Descripción |
|--------|-------------------|-------------|
| `core` | Siempre activo | Bootstrap, providers base, helpers, Twig. |
| `blog` | Activado | Templates para posts, archive, categorías, tags, búsqueda. |
| `pages` | Activado | Soporte para páginas estáticas con campos custom. |
| `example_cpt` | Desactivado | Custom Post Type de ejemplo (ej. "Podcast") con su archive y single. |
| `forms` | Activado | Formulario de contacto con validación Rakit y HTMX. |
| `seo` | Activado | JSON-LD organización y artículos vía Spatie Schema. |
| `design_system` | Activado en dev | Ruta `/__components` para visualizar componentes Twig. |

### 5.1 Ejemplo de API de configuración

```php
<?php
// app/Config/features.php

return [
    'blog' => [
        'enabled' => true,
        'posts_per_page' => 12,
        'show_author' => true,
        'show_categories' => true,
    ],
    'example_cpt' => [
        'enabled' => false,
        'slug' => 'podcast',
        'singular' => 'Podcast',
        'plural' => 'Podcasts',
    ],
    'forms' => [
        'enabled' => true,
        'contact_recipient' => 'admin_email',
    ],
    'seo' => [
        'enabled' => true,
        'organization_name' => 'carbon_get_theme_option:rs_company_name',
    ],
    'design_system' => [
        'enabled' => ($_ENV['WP_ENV'] ?? '') === 'development',
    ],
];
```

---

## 6. Estructura de carpetas propuesta

```text
rojas-starter/
├── .ddev/                       # Configuración DDEV
├── .github/
│   └── workflows/               # CI: lint, test, build, deploy
├── .lefthook.yml                # Git hooks
├── AGENTS.md                    # Guía para agentes de IA
├── README.md                    # Documentación principal
├── composer.json                # Dependencias PHP
├── functions.php                # Bootstrap del tema
├── package.json                 # Dependencias Node/Bun
├── postcss.config.js            # Configuración PostCSS
├── style.css                    # Cabecera oficial del tema
├── tailwind.config.js           # Configuración Tailwind (si aplica para v4)
├── vite.config.js               # Configuración Vite
├── app/
│   ├── Config/
│   │   └── features.php         # Configuración central de módulos
│   ├── Contracts/               # Interfaces
│   ├── Controllers/
│   │   ├── BlogController.php
│   │   ├── ContactController.php (o FormController.php)
│   │   └── DesignSystemController.php
│   ├── Fields/
│   │   ├── ThemeOptions.php
│   │   └── PageFields.php
│   ├── Models/                  # Modelos de dominio del proyecto (opcional)
│   ├── Providers/
│   │   ├── AssetServiceProvider.php (usa kucrut/vite-for-wp)
│   │   ├── MenuServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── SeoServiceProvider.php
│   ├── Setup/
│   │   └── ThemeSetup.php       # Orquestador
│   └── helpers.php              # Helpers globales
├── config/                      # Configuración de herramientas QA
│   ├── phpstan.neon
│   ├── pint.json
│   └── ...
├── resources/
│   ├── scripts/
│   │   └── main.js              # Alpine + HTMX
│   ├── styles/
│   │   └── main.css             # Tailwind v4 + tokens
│   └── views/
│       ├── base.twig
│       ├── index.twig
│       ├── single.twig
│       ├── blog.twig
│       ├── contact.twig
│       ├── components/          # Componentes reutilizables Twig
│       │   ├── header.twig
│       │   ├── footer.twig
│       │   ├── search-modal.twig
│       │   ├── button.twig
│       │   ├── card.twig
│       │   └── ...
│       ├── partials/            # Fragmentos parciales
│       │   ├── contact-form.twig
│       │   └── search-results.twig
│       └── design-system/       # Documentación visual de componentes
│           └── index.twig
├── tests/
│   ├── Unit/                    # Tests Pest/PHPUnit
│   │   └── ExampleTest.php
│   └── E2E/                     # Tests Playwright
│       └── contact-form.spec.js
└── e2e/                         # (legacy) o renombrar a tests/E2E
```

---

## 7. Sistema de build y assets

### 7.1 Vite + `kucrut/vite-for-wp`
Reemplazar el `AssetServiceProvider` manual actual por una integración con la librería oficial.

#### Instalación
```bash
composer require kucrut/vite-for-wp
bun add -D @kucrut/vite-for-wp
```

#### `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import { v4wp } from '@kucrut/vite-for-wp';
import path from 'path';

export default defineConfig({
  plugins: [
    v4wp({
      input: path.resolve(__dirname, 'resources/scripts/main.js'),
      outDir: path.resolve(__dirname, 'dist'),
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources'),
    },
  },
});
```

#### Encolado en PHP
```php
use Kucrut\Vite;

add_action('wp_enqueue_scripts', function (): void {
    Vite\enqueue_asset(
        __DIR__ . '/dist',
        'resources/scripts/main.js',
        [
            'handle' => 'rojas-starter',
            'in-footer' => true,
        ]
    );
});
```

### 7.2 Scripts NPM/Bun
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "lint": "biome check . && composer lint",
    "fix": "biome check --apply . && composer fix",
    "test": "pest",
    "test:e2e": "playwright test"
  }
}
```

### 7.3 Tailwind CSS v4
- Usar `@import "tailwindcss"` en `resources/styles/main.css`.
- Definir tokens de marca en `@theme { ... }`.
- Aprovechar `@tailwindcss/postcss` como plugin de PostCSS.

---

## 8. Sistema de diseño

### 8.1 Tokens de diseño
Definidos en CSS con `@theme` y/o variables CSS para facilitar dark mode y personalización.

```css
@theme {
  --color-brand-50: #eff6ff;
  --color-brand-100: #dbeafe;
  /* ... */
  --color-brand-950: #172554;

  --font-sans: 'Inter', system-ui, sans-serif;
  --font-serif: 'Merriweather', Georgia, serif;
}
```

### 8.2 Dark mode
- Implementado con media query `prefers-color-scheme: dark`.
- No hay toggle manual en frontend.
- Las variables y tokens deben tener contrapartes dark.

```css
@media (prefers-color-scheme: dark) {
  :root {
    --color-bg: #0f172a;
    --color-text: #f8fafc;
  }
}
```

### 8.3 Componentes Twig base
- `components/button.twig` — variante primary, secondary, ghost.
- `components/card.twig` — tarjeta de blog/portfolio.
- `components/input.twig` — input con estados de error.
- `components/alert.twig` — mensajes de éxito/error.
- `components/pagination.twig` — paginación de posts.
- `components/badge.twig` — etiquetas de categorías/tags.

### 8.4 Design system route
Ruta virtual `/__components` disponible solo en desarrollo para renderizar todos los componentes con diferentes estados.

---

## 9. Estrategia de calidad y testing

### 9.1 Static analysis y linting
- **PHPStan:** nivel máximo alcanzable progresivamente.
- **Laravel Pint:** formateo automático de PHP.
- **Biome:** linting y formateo de JS/CSS.

### 9.2 Tests unitarios
- **Pest/PHPUnit** bajo `tests/Unit/`.
- Cubrir al menos lógica de providers, helpers y validadores de formularios.

### 9.3 Tests E2E ✅
- **Playwright** bajo `e2e/` (el spec original decía `tests/E2E/`; la
  ubicación real es `e2e/` en la raíz, junto a `playwright.config.js`).
- Escenarios mínimos (el estado de cada uno se mantiene como parte viva
  de este documento):
  - ~~Carga de home.~~ ✅ (`e2e/home.spec.js`)
  - ~~Navegación del menú.~~ ✅ (cubierto en `e2e/home.spec.js` + nav del header en la suite)
  - ~~Envío de formulario de contacto vía HTMX.~~ ✅ (envío del form engine vía HTMX)
  - ~~Búsqueda en tiempo real.~~ ✅ (2026-09-08, commit `228d73c`: endpoint `admin-ajax?action=rs_live_search` → fragmento `partials/search-results.twig` con `hx-swap`; e2e en `e2e/search.spec.js` — resultado con enlace, estado sin resultados y término vacío sin tocar la DB)
  - ~~Dark mode visual correcto.~~ ✅ (`e2e/dark-mode.spec.js`, tokens semánticos por `prefers-color-scheme`)
  - *(Adicional, fuera del mínimo del spec: `e2e/design-system.spec.js` — catálogo `/__components`; `e2e/blog.spec.js` — archive con paginación)*

### 9.4 Git hooks
- **Lefthook** para ejecutar linting y tests rápidos pre-commit.
- Bloquear commit si falla `composer lint` o `bun run lint`.

---

## 10. Plan de documentación

| Documento | Contenido |
|-----------|-----------|
| `README.md` | Instalación, comandos, estructura, despliegue. |
| `AGENTS.md` | Reglas de orquestación para agentes de IA. |
| `docs/COMPONENTS.md` | Catálogo de componentes Twig y tokens. |
| `docs/ARCHITECTURE.md` | Decisiones de arquitectura y flujo de datos. |
| `docs/DEPLOYMENT.md` | Guía de despliegue con Deployer y DDEV. |
| Ruta `/__components` | Documentación visual interactiva. |

---

## 11. Distribución y licenciamiento

### 11.1 Distribución
- **GitHub Template Repository:** permitir "Use this template".
- **Packagist:** `composer create-project cristian-rojas/rojas-starter mi-proyecto`.
- **No WordPress.org** en esta versión.

### 11.2 Licencia
- **GPL v2 o v3** para compatibilidad con WordPress y comunidad open source.
- **Decisión (2026-09-08):** `GPL-2.0-or-later` (SPDX) — texto oficial de GPL v2 en `LICENSE`, headers de `composer.json` y `style.css` alineados. Motivo: un tema de WP es obra derivada de WordPress (GPL) y hereda su licencia; "or-later" preserva la opción v3.

---

## 12. Ruta de migración desde el blueprint actual

> **Estado (2026-09-08):** pasos 1–11 completados y verificados
> (25 tests Pest, 7 escenarios e2e Playwright, CI en GitHub Actions,
> Lefthook pre-commit). La implementación real difiere de este plan
> en los puntos documentados en §12.1.

1. ~~**Auditar código existente:** eliminar dependencias no usadas y consolidar providers.~~ ✅
2. ~~**Reemplazar asset provider:** migrar de `AssetServiceProvider` manual a `kucrut/vite-for-wp`.~~ ✅
3. ~~**Crear sistema de configuración central:** mover configuraciones hardcodeadas a `app/Config/features.php`.~~ ✅
4. ~~**Estandarizar componentes Twig:** extraer botones, inputs, tarjetas y alertas en `resources/views/components/`.~~ ✅ (con módulo `design_system` y ruta `/__components` activos)
5. ~~**Implementar dark mode:** agregar media query `prefers-color-scheme: dark` y tokens oscuros.~~ ✅ (tokens semánticos runtime + `@theme inline`)
6. ~~**Crear Example CPT:** añadir un custom post type de ejemplo desactivado por defecto.~~ ✅ (`ExampleCptServiceProvider` + `archive-podcast.twig` / `single-podcast.twig`)
7. ~~**Evolucionar contacto a form engine:** generalizar `ContactController` a `FormServiceProvider` o `FormController` configurable.~~ ✅ (`FormController` genérico: cada formulario es una entrada en `features.php` con ruta, campos/reglas Rakit, destinatario y fragmento HTMX)
8. ~~**Configurar testing:** crear `tests/Unit/` y `tests/E2E/`, agregar Pest y Playwright.~~ ✅ (Pest + Brain Monkey + Patchwork; Playwright con Chrome del sistema)
9. ~~**Búsqueda en vivo HTMX:** endpoint de fragmento para el modal de búsqueda (§9.3).~~ ✅ (2026-09-08, commit `228d73c`: `SearchController` + `partials/search-results.twig`; `search.php` queda full-page-only — un solo camino por contrato de fragmentos)
10. ~~**Configurar git hooks:** añadir `.lefthook.yml` con linting y tests.~~ ✅ (`lefthook.yml`: biome + phpstan + pest por archivos staged)
11. ~~**Documentar:** expandir `README.md`, crear/actualizar `AGENTS.md`, docs de componentes y ruta `/__components`.~~ ✅ (README reescrito, `docs/ARCHITECTURE.md` con flujo de búsqueda en §4.4, `docs/COMPONENTS.md`, catálogo vivo en `/__components`)
12. **Publicar:** configurar GitHub Template y Packagist, añadir `LICENSE` GPL. ⬜ (LICENSE GPL-2.0-or-later añadido + headers alineados; pendiente: GitHub Template y Packagist)

### 12.1 Desviaciones de la implementación respecto a este plan

- **Jerarquía de templates (crítico):** el blueprint original tenía un
  `index.php` que renderizaba `index.twig` para toda petición,
  bloqueando la jerarquía de WordPress (`archive-{post_type}.twig`,
  `single-{post_type}.twig`, ...). El fix delega la resolución a la
  jerarquía de Timber desde `index.php`: las rutas virtuales del
  `RouteServiceProvider` siguen teniendo prioridad (interceptan en
  `template_redirect`, antes de la resolución de templates), y todo lo
  demás (archives, singles, home) resuelve nativamente. **Regla del
  stack:** el router es para páginas 100% code-first; el contenido de
  la DB usa la jerarquía Twig. Esto responde también la pregunta de
  §13 sobre rutas virtuales vs. jerarquía: conviven, con la jerarquía
  como base y el router como override explícito.
- **Detección de dev server:** en vez del archivo `hot` en la raíz del
  tema (enfoque manual original), `kucrut/vite-for-wp` usa
  `dist/vite-dev-server.json`, escrito/limpiado por el plugin
  `dev_server()` de `@kucrut/vite-for-wp` mientras `bun run dev` corre.
  Gotcha operativo: si el dev server muere abruptamente (kill -9), el
  manifest queda huérfano y el sitio sigue apuntando a URLs muertas
  hasta borrarlo a mano.
- **Dependencias:** los wildcards (`*`/`latest`) de la fase experimental
  fueron reemplazados por **rangos caret anclados** a las versiones
  verificadas (2026-09-08); `composer.lock` y `bun.lock` siguen siendo
  el mecanismo de reproducibilidad exacta. En el mismo paso se retiró
  `jgrossi/corcel` (sin uso en el código y con su dependencia
  `hautelook/phpass 0.3.4` yanked en Packagist) y `lucatume/wp-browser`
  (sin uso, bloqueaba estructuralmente Pest 5).

---

## 13. Preguntas abiertas y supuestos

- **Versionado de dependencias:** resuelto (2026-09-08) — rangos caret anclados en `composer.json` (ver §12.1); los majors quedan como decisiones explícitas de upgrade.
- **Multilenguaje:** aunque se eligió "plugin-agnóstico", ¿debe existir un ejemplo mínimo de internacionalización (i18n) listo para WPML/Polylang?
- **Rutas virtuales vs. jerarquía de templates:** respondido en §12.1 — conviven: la jerarquía Twig es la base (contenido de la DB) y el `RouteServiceProvider` es override explícito code-first (intercepta en `template_redirect`).
- **Generador de POT:** ¿incluir un workflow/script para generar archivos `.pot` de traducción?
- **CLI interno:** ¿vale la pena incluir un comando propio (ej. `wp rojas-starter make:component`) o mantenerlo manual?

---

## 14. Criterios de éxito

- [ ] Un nuevo proyecto se puede crear con `composer create-project` o "Use this template" en menos de 5 minutos. *(verificado manualmente desde clone limpio en la pre-publish sweep; pendiente la mecánica de GitHub Template/Packagist)*
- [x] `ddev start && bun install && bun run dev` levanta el entorno con HMR funcional (verificado con `kucrut/vite-for-wp`).
- [x] El tema pasa `composer lint`, `bun run lint`, `pest` y `playwright test` sin errores (25 tests Pest + 7 e2e Playwright + PHPStan nivel 6 + Biome).
- [x] Todos los módulos son activables/desactivables desde `app/Config/features.php` (blog, pages, forms, seo, example_cpt, design_system).
- [x] La documentación (`README.md`, `AGENTS.md`, docs de componentes) permite que un desarrollador nuevo entregue una página en menos de 1 hora.

---

*Especificación generada tras entrevista de requerimientos. Sujeta a refinamiento.*
