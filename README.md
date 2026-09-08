# **🚀 Rojas Stack — Starter Theme WordPress Moderno (AI-First)**

[![CI](https://github.com/cristian-rojas/wp-modern-theme-blueprint/actions/workflows/ci.yml/badge.svg)](https://github.com/cristian-rojas/wp-modern-theme-blueprint/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.5%2B-777bb4)](composer.json)
[![Timber](https://img.shields.io/badge/Timber-Twig-93c47d)](https://timber.github.io/timber/)
[![License](https://img.shields.io/badge/License-GPL_v2-blue)](LICENSE)

Starter theme WordPress **monolítico moderno, 100% code-first**: PHP estricto + Twig (Timber), Vite con HMR, Tailwind v4 con dark mode automático, HTMX + AlpineJS, y un sistema de módulos activables desde un solo archivo de configuración. Sin Gutenberg, sin page builders, sin plugins de UI.

> **Documentación completa:** [Arquitectura](docs/ARCHITECTURE.md) · [Componentes y tokens](docs/COMPONENTS.md) · [Guía para agentes de IA](AGENTS.md) · [Spec](rojas-starter-spec.md)

## **✨ Características**

- **⚡ DX moderna:** Vite + HMR total (via [`kucrut/vite-for-wp`](https://github.com/kucrut/vite-for-wp)), DDEV, Biome, PHPStan, Pest, Playwright, Lefthook, CI en GitHub Actions.
- **🧩 Módulos activables:** blog, pages, forms, seo, example_cpt y design_system se activan/desactivan y parametrizan desde `app/Config/features.php`. Un módulo deshabilitado no registra rutas, hooks ni campos.
- **🧱 Componentes Twig:** button, card, input, alert con API documentada por props y catálogo visual en `/__components` (solo dev).
- **🌙 Dark mode automático:** tokens semánticos CSS con contrapartes dark vía `prefers-color-scheme` — sin toggle, sin duplicar clases.
- **📨 Form engine config-driven:** cada formulario es una entrada en `features.php` (ruta, campos, reglas Rakit, destinatario, fragmento HTMX de éxito). Añadir un formulario = cero código nuevo.
- **🧠 AI-First:** tipado estricto, docblocks con tipos, estructura predecible y `AGENTS.md` como constitución para agentes.

## **🛠 Stack**

| Capa | Tecnologías |
| :--- | :--- |
| **Backend** | PHP 8.5+, Composer, Timber (Twig), Carbon Fields, Rakit Validation |
| **Frontend** | Bun, Vite, Tailwind CSS v4, HTMX, AlpineJS |
| **QA** | PHPStan (nivel 6), Biome, Pest + Brain Monkey + Patchwork, Playwright, Spatie Ignition |
| **DevOps** | DDEV, GitHub Actions, Deployer, Lefthook |

## **📂 Estructura**

```text
├── app/
│   ├── Config/features.php   # ← Única fuente de verdad de módulos
│   ├── Controllers/          # Orquestan datos → Twig (Blog, Form, DesignSystem)
│   ├── Fields/               # Carbon Fields code-first (ThemeOptions, PageFields)
│   ├── Providers/            # Hooks WP (Assets, Menus, Router, SEO, ExampleCpt)
│   └── Setup/ThemeSetup.php  # Orquestador: core + módulos habilitados
├── resources/
│   ├── scripts/main.js       # Alpine + HTMX (importa main.css)
│   ├── styles/main.css       # Tailwind v4 + tokens semánticos
│   └── views/                # Twig: base, index, blog, single, contact,
│       └── components/       #   button, card, input, alert, header, footer...
├── docs/                     # ARCHITECTURE.md, COMPONENTS.md
├── e2e/                      # Playwright (smoke tests)
├── tests/Unit/               # Pest (sin WordPress real)
├── functions.php             # Bootstrap: autoload, .env, Timber, Carbon, ThemeSetup
└── style.css                 # Cabecera oficial del tema
```

## **🚀 Inicio rápido**

Requisitos: **Docker + DDEV**, **Bun** y **Composer** en el host (PHP 8.5 vive en el contenedor DDEV).

```bash
# 1. Entorno (MariaDB, Valkey, Mailpit, web)
ddev start

# 2. Dependencias
composer install
bun install

# 3. Build de assets (o modo dev con HMR)
bun run build
```

El sitio queda en `https://<nombre-proyecto>.ddev.site`. El "core fantasma" de WordPress (core fuera del repo, tema enlazado) se detecta y activa automáticamente en `ddev start`.

### **Modo desarrollo (HMR)**

```bash
bun run dev
```

Los assets se sirven desde el dev server de Vite con hot reload de JS y Twig/PHP. ⚠️ Si el dev server muere abruptamente (crash, `kill -9`), borra el manifest huérfano: `rm dist/vite-dev-server.json`.

## **🧩 Configuración de módulos**

Todo se activa/parametriza en `app/Config/features.php`:

```php
// El valor global POSTS_PER_PAGE (.env, default 6) alimenta todos los
// feeds; cada módulo puede overridearlo por instancia:
'posts_per_page' => 6,

'blog' => [
    'enabled' => true,
    'posts_per_page' => 6,
],

'forms' => [
    'enabled' => true,
    'forms' => [
        'contact' => [
            'route' => '/contacto',
            'fields' => [
                'name'    => 'required|min:3',
                'email'   => 'required|email',
                'message' => 'required|min:10',
            ],
            // ... recipient, subject, templates, textos de éxito
        ],
        // Añadir 'newsletter' => [...] = segundo formulario, sin código nuevo
    ],
],

'example_cpt' => [
    'enabled' => false,       // Actívalo para el CPT de ejemplo "Podcast"
    'slug' => 'podcast',
    'singular' => 'Podcast',
    'plural' => 'Podcasts',
],
```

## **⚡ Comandos**

| Tarea | Comando |
| :--- | :--- |
| Levantar entorno | `ddev start` |
| Modo dev (HMR) | `bun run dev` |
| Build de producción | `bun run build` |
| Tests unitarios (Pest) | `ddev exec composer test` |
| Análisis estático (PHPStan 6) | `ddev exec composer analyze` |
| Lint JS/CSS (Biome) | `bun run lint` (auto-fix: `bun run fix`) |
| Tests E2E (Playwright) | `bun run test:e2e` |
| Desplegar a producción | `ddev exec dep deploy production` |

Los E2E corren contra el sitio DDEV activo y usan el Chrome del sistema. Para apuntar a otro entorno:

```bash
PLAYWRIGHT_BASE_URL=https://staging.example.com bun run test:e2e
```

## **🎨 Componentes**

El catálogo vivo está en la ruta `/__components` (activa con `WP_ENV=development`). La referencia completa de props y tokens semánticos está en [`docs/COMPONENTS.md`](docs/COMPONENTS.md):

```twig
{% include 'components/card.twig' with {
    title: post.title,
    url: post.link,
    excerpt: post.preview.length(20),
    image: post.thumbnail ? post.thumbnail.src|resize(600, 400) : null,
    meta: post.date|date('d M, Y'),
} only %}
```

## **🚢 Despliegue (theme-only)**

El repo contiene **solo el tema**: el core de WordPress mantiene sus actualizaciones automáticas de seguridad en producción. [Deployer](https://deployer.org) gestiona releases atómicas vía symlinks con `shared/` para `wp-content/uploads` y `.env`.

```bash
ddev exec dep deploy production
```

El pipeline de CI (`.github/workflows/ci.yml`) ejecuta Biome + build Vite, y PHPStan + Pest en cada push/PR. Los pre-commits (Lefthook) corren biome + phpstan + pest localmente.

## **🤖 Agentes de IA**

La arquitectura está optimizada para colaboración humano-agente: [`AGENTS.md`](AGENTS.md) define las reglas del stack (escritura nativa de WP, nonce HTMX, HTMX vs Alpine, contrato de fragmentos), los tipos están en cada firma y el catálogo de componentes es introspectable en `/__components`.

## **📜 Licencia**

**GPL v2 o posterior** — ver [LICENSE](LICENSE). El tema es una obra derivada de WordPress (GPL) y la licencia hereda; la palabra "or-later" deja a los usuarios la opción de usarlo bajo GPL v3.

---

### **Diseñado por Cristian Rojas (El Arquitecto)**
*Optimizado para la era de la web moderna, la soberanía tecnológica y la colaboración eficiente con IA.*
