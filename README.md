# **🚀 Rojas Stack — Starter Theme WordPress Moderno (AI-First)**

[![CI](https://github.com/RojasCristianE/wp-modern-theme-blueprint-rojas-stack/actions/workflows/ci.yml/badge.svg)](https://github.com/RojasCristianE/wp-modern-theme-blueprint-rojas-stack/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%2010%20(max)-8892BF)](phpstan.neon)
[![PHP](https://img.shields.io/badge/PHP-8.5%2B-777bb4)](composer.json)
[![Timber](https://img.shields.io/badge/Timber-Twig-93c47d)](https://timber.github.io/timber/)
[![License](https://img.shields.io/badge/License-GPL_v2-blue)](LICENSE)

Starter theme WordPress **monolítico moderno, 100% code-first**: PHP estricto + Twig (Timber), Vite con HMR, Tailwind v4 con dark mode automático, HTMX + AlpineJS, y un sistema de módulos activables desde un solo archivo de configuración. Sin Gutenberg, sin page builders, sin plugins de UI.

> **Documentación completa:** [Arquitectura](docs/ARCHITECTURE.md) · [Componentes y tokens](docs/COMPONENTS.md) · [Guía para agentes de IA](AGENTS.md) · [Spec](docs/spec.md)

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
| **QA** | PHPStan (nivel 10 — máximo), Biome, Pest + Brain Monkey + Patchwork, Playwright, Spatie Ignition |
| **DevOps** | DDEV, GitHub Actions, Deployer, Lefthook |

## **✅ Quality Gates**

Tres capas ejecutan la misma batería: Lefthook en cada commit local, CI en cada push/PR — misma configuración, cero deriva.

| Gate | Alcance | Comando local | CI |
| :--- | :--- | :--- | :--- |
| **PHPStan nivel 10** (máximo) | `app/` + `tests/` — mixed explícito, cero suppressions, sin baseline | `ddev exec composer analyze` | ✅ `backend` |
| **Pest** (unit) | 41 tests, Brain Monkey + Patchwork, sin WP real | `ddev exec composer test` | ✅ `backend` |
| **Biome** | JS/CSS — lint + format | `bun run lint` | ✅ `frontend` |
| **Vite build** | bundle de producción | `bun run build` | ✅ `frontend` |
| **Playwright** (e2e) | 14 escenarios contra DDEV | `bun run test:e2e` | — local (requiere DDEV) |
| **Lefthook** | biome + pest + phpstan en cada commit | automático (git hooks) | — |

## **📂 Estructura**

> Esto NO es un tema de WordPress tradicional: los archivos PHP en raíz
> son solo las puertas obligatorias de WP; el código real vive en
> directorios dedicados. Los archivos/puntos que aparecen solo al correr
> el entorno se listan al final.

```text
├── app/                      # Lógica PHP estricta (la mayor parte del tema)
│   ├── Config/features.php   # ← Única fuente de verdad de módulos
│   ├── Contracts/            # Interfaces (SearchEngineInterface...)
│   ├── Controllers/          # Orquestan datos → Twig (Blog, Form, Search, Archive)
│   ├── Fields/               # Carbon Fields code-first (ThemeOptions, PageFields)
│   ├── Providers/            # Hooks WP (Assets, Menus, Router, SEO, ExampleCpt)
│   ├── Services/             # Integraciones (Meilisearch client + indexer)
│   └── Setup/ThemeSetup.php  # Orquestador: core + módulos habilitados
├── resources/
│   ├── scripts/main.js       # Alpine + HTMX (importa main.css)
│   ├── styles/main.css       # Tailwind v4 (@theme) + tokens semánticos
│   └── views/                # Twig: base, index, blog, single, contact,
│       └── components/       #   button, card, input, alert, header, footer...
├── bin/                      # Scripts de mantenimiento (reindex Meilisearch, seed)
├── docs/                     # ARCHITECTURE.md, COMPONENTS.md, spec.md
├── e2e/                      # Playwright (smoke tests)
├── infra/                    # Config de servidor para deploy (wp-config.example.php)
├── tests/Unit/               # Pest (sin WordPress real)
├── functions.php             # Bootstrap: autoload, .env, Timber, Carbon, ThemeSetup
├── index.php                 # Fallback WP → resuelve la jerarquía Twig
├── search.php / single.php   # Controladores de la jerarquía (búsqueda, single)
├── style.css                 # Cabecera oficial del tema (sin CSS real)
└── vite.config.js            # Build: Tailwind v4 inline + vite-for-wp
```

**Aparecen al correr (gitignored, no hace falta entenderlos):**
`dist/` (build), `node_modules/` + `vendor/` (deps), `wp-config.php` +
`wp-config-ddev.php` (los escribe DDEV), `.cache/`, `test-results/`.

## **🚀 Inicio rápido**

Requisitos: **Docker + DDEV**, **Bun** y **Composer** en el host (PHP 8.5 vive en el contenedor DDEV).

```bash
# 1. Entorno (MariaDB, Valkey, Mailpit, Meilisearch)
ddev start

# 2. Dependencias
composer install
bun install

# 3. Build de assets (o modo dev con HMR)
bun run build

# 4. (Opcional) Contenido demo: 15 posts con categorías/etiquetas
# para que la paginación (blog + archives) tenga varias páginas.
# Idempotente; necesario para los e2e de paginación en installs frescos.
bash bin/seed-demo-content.sh
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
// El valor global POSTS_PER_PAGE (.env, default 6) alimenta TODAS las
// superficies: controllers del tema Y archives nativos (categoría,
// etiqueta y home) vía un hook pre_get_posts — una sola fuente de
// verdad. Cada módulo puede overridearlo por instancia:
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
| Análisis estático (PHPStan 10) | `ddev exec composer analyze` |
| Lint JS/CSS (Biome) | `bun run lint` (auto-fix: `bun run fix`) |
| Tests E2E (Playwright) | `bun run test:e2e` |
| Datos demo (paginación) | `bash bin/seed-demo-content.sh` |
| Sincronizar iconos (tras actualizar sets) | `bun run icons:sync` |
| Reindexar búsqueda (Meilisearch) | `bash bin/meili-reindex.sh` |
| Desplegar a producción | `ddev exec dep deploy production` |

Los E2E corren contra el sitio DDEV activo y usan el Chrome del sistema. Los escenarios de paginación (blog, categoría y etiqueta) requieren el contenido demo: ejecuta `bash bin/seed-demo-content.sh` una vez — el valor de `POSTS_PER_PAGE` del `.env` gobierna también los archives nativos, así que los recuentos de páginas son determinísticos. Para apuntar a otro entorno:

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
