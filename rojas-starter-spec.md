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
- **Modelos:** Corcel/Eloquent para lectura de datos (read-only).
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
│   ├── Models/                  # Modelos Corcel
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

### 9.3 Tests E2E
- **Playwright** bajo `tests/E2E/`.
- Escenarios mínimos:
  - Carga de home.
  - Navegación del menú.
  - Envío de formulario de contacto vía HTMX.
  - Búsqueda en tiempo real.
  - Dark mode visual correcto.

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

---

## 12. Ruta de migración desde el blueprint actual

1. **Auditar código existente:** eliminar dependencias no usadas y consolidar providers.
2. **Reemplazar asset provider:** migrar de `AssetServiceProvider` manual a `kucrut/vite-for-wp`.
3. **Crear sistema de configuración central:** mover configuraciones hardcodeadas a `app/Config/features.php`.
4. **Estandarizar componentes Twig:** extraer botones, inputs, tarjetas y alertas en `resources/views/components/`.
5. **Implementar dark mode:** agregar media query `prefers-color-scheme: dark` y tokens oscuros.
6. **Crear Example CPT:** añadir un custom post type de ejemplo desactivado por defecto.
7. **Evolucionar contacto a form engine:** generalizar `ContactController` a `FormServiceProvider` o `FormController` configurable.
8. **Configurar testing:** crear `tests/Unit/` y `tests/E2E/`, agregar Pest y Playwright.
9. **Configurar git hooks:** añadir `.lefthook.yml` con linting y tests.
10. **Documentar:** expandir `README.md`, crear/actualizar `AGENTS.md`, docs de componentes y ruta `/__components`.
11. **Publicar:** configurar GitHub Template y Packagist, añadir `LICENSE` GPL.

---

## 13. Preguntas abiertas y supuestos

- **Versionado de dependencias:** ¿se deben fijar versiones en `composer.json`/`package.json` para estabilidad, o mantener wildcards y confiar en que cada proyecto gestione updates?
- **Multilenguaje:** aunque se eligió "plugin-agnóstico", ¿debe existir un ejemplo mínimo de internacionalización (i18n) listo para WPML/Polylang?
- **Rutas virtuales vs. jerarquía de templates:** ¿permitir que `RouteServiceProvider` sobrescriba slugs de posts/páginas existentes, o respetar siempre el contenido de la DB?
- **Generador de POT:** ¿incluir un workflow/script para generar archivos `.pot` de traducción?
- **CLI interno:** ¿vale la pena incluir un comando propio (ej. `wp rojas-starter make:component`) o mantenerlo manual?

---

## 14. Criterios de éxito

- [ ] Un nuevo proyecto se puede crear con `composer create-project` o "Use this template" en menos de 5 minutos.
- [ ] `ddev start && bun install && bun run dev` levanta el entorno con HMR funcional.
- [ ] El tema pasa `composer lint`, `bun run lint`, `pest` y `playwright test` sin errores.
- [ ] Todos los módulos son activables/desactivables desde `app/Config/features.php`.
- [ ] La documentación (`README.md`, `AGENTS.md`, docs de componentes) permite que un desarrollador nuevo entregue una página en menos de 1 hora.

---

*Especificación generada tras entrevista de requerimientos. Sujeta a refinamiento.*
