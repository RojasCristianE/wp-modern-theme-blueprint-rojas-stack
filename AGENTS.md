# **🚀 Rojas Stack: Modern Theme Architecture for WordPress (AI-First)**

Este documento define la arquitectura y filosofía del **Rojas Stack**, un entorno de ingeniería diseñado para el desarrollo de temas de WordPress "Monolíticos Modernos". Se prioriza la separación de lógica y vista, el tipado estricto, la predictibilidad y el uso de tecnologías Open Source comunitarias, creando un ecosistema optimizado para humanos y agentes de IA.

> **Documentación:** [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) (decisiones y flujo de datos) · [docs/COMPONENTS.md](docs/COMPONENTS.md) (catálogo de componentes y tokens) · Catálogo visual en la ruta `/__components` (solo dev).

## **🧠 Filosofía del Stack**

* **Theme-Centric Control:** El control total reside en el código del tema, minimizando la dependencia del CMS y plugins externos.
* **Separación de Capas (Responsabilidad Única):** Lógica encapsulada en PHP y vistas atómicas en Twig.
* **AI-First & Predictibilidad:** Estructuras predecibles, tipado estricto (Contracts/Carbon Fields) y auto-documentación in-situ para minimizar alucinaciones y fricción en el desarrollo colaborativo.
* **Soberanía Tecnológica (OSS Friendly):** Uso de alternativas de código abierto real (Valkey, MariaDB) y herramientas estándar de la industria (Composer, Bun, Vite).
* **Despliegue Ágil (Theme-Only):** El repositorio se enfoca exclusivamente en el código del tema, permitiendo que el Core de WordPress mantenga sus actualizaciones automáticas de seguridad intactas en producción.

## **🛠 Stack Tecnológico (The Dream Team)**

### **1. Backend & Data (PHP & Composer)**
* **Timber (Twig):** Motor de plantillas desacoplado; `Timber::get_posts()`/`WP_Query` para lectura.
* **Carbon Fields:** Metadatos 100% Code-First.
* **MariaDB & Valkey:** Base de datos y caché de alto rendimiento.
* **Action Scheduler:** Sistema de colas robusto.
* **Illuminate HTTP & Carbon:** Utilidades modernas.

### **2. Frontend & Interacción (Bun & Vite)**
* **Vite + vite-plugin-live-reload:** Bundler con HMR total.
* **Tailwind CSS + Flowbite:** Diseño atómico y accesible.
* **HTMX + AlpineJS:** Interactividad reactiva sin la complejidad de SPAs.

### **3. CI/CD & Despliegue del Tema**
* **Deployer:** Herramienta de despliegue atómico "Zero-Downtime" vía SSH. Gestiona los releases del tema mediante enlaces simbólicos.
* **GitHub Actions:** Pipeline de automatización (Lint -> Test -> Deployer).
* **Semantic Release:** Versionado automático basado en Conventional Commits.
* **vlucas/phpdotenv:** Gestión de secretos mediante `$_ENV`.

### **4. QA & Debugging**
* **PHPStan, Laravel Pint & Biome:** Triada de calidad de código.
* **Playwright:** E2E Testing moderno.
* **Spatie Ignition & Clockwork:** Debugging profundo.
* **Lefthook:** Orquestador de Git Hooks concurrentes.

## **🌐 Arquitectura Macro (Infraestructura del Servidor)**
Aunque este blueprint se enfoca en el desarrollo del tema, asume que el servidor anfitrión (Producción/Staging) puede estar gobernado por una infraestructura moderna externa:
* **Entorno Local:** **DDEV** (Provee contenedores aislados de MariaDB, Valkey, Mailpit y Servidor Web).
* **Orquestación de Servidor (Opcional/Externa):** El ecosistema global de sitios puede ser gestionado por un repositorio de infraestructura independiente usando **Kamal**, **Traefik** y **Caddy**, proveyendo contenedores base de WordPress que reciben despliegues vía Deployer.
* **Estado Compartido (Shared State):** Deployer **debe** configurar directorios compartidos (`shared/`) para `wp-content/uploads` y el archivo `.env`, asegurando que los datos persistentes sobrevivan a cada nuevo despliegue.

## **📂 Estructura del Tema (`app/` y `resources/`)**

```text
wp-modern-theme-blueprint/
├── .ddev/                # Configuración de desarrollo local.
├── .github/workflows/    # Pipelines de CI/CD (Test, Deploy).
├── app/                  # Lógica de Negocio (PHP Strict)
│   ├── Config/           # features.php: módulos activables (spec §5).
│   ├── Contracts/        # Interfaces (El manual para la IA).
│   ├── Controllers/      # Controladores (Orquestación).
│   ├── Fields/           # Definiciones de Carbon Fields.
│   ├── Models/           # Modelos de dominio del proyecto (opcional).
│   ├── Providers/        # Service Providers (WordPress Hooks).
│   ├── Services/         # Lógica pesada (APIs, Cache Purging).
│   └── Setup/            # ThemeSetup: orquestador de core + módulos.
├── resources/            # Assets y Vistas (Vite/Twig)
├── e2e/                  # Tests con Playwright.
├── infra/                # Archivos de configuración de servidor.
├── deploy.php            # Configuración de despliegue con Deployer.
├── functions.php         # Bootstrap del tema.
├── composer.json         # Dependencias PHP.
├── package.json          # Dependencias Node/Bun.
└── style.css             # Cabecera oficial del tema.
```

## **🤝 Reglas de Integración Front-Back (Safe Rules)**

1.  **Integridad de Datos (Write-Native):** La **escritura** de datos usa siempre funciones nativas (`wp_insert_post`) para respetar los hooks; la lectura pasa por `Timber::get_posts()`/`WP_Query`.
2.  **Seguridad HTMX (X-WP-Nonce):** Todas las peticiones HTMX hacia la REST API de WordPress **deben incluir el nonce de seguridad**. Esto se configura inyectando globalmente la cabecera `X-WP-Nonce` mediante el evento `htmx:configRequest`.
3.  **Gestión de Estado (HTMX vs Alpine):** El estado persistente (filtros, paginación) reside en el servidor. **AlpineJS se reserva estrictamente para micro-interacciones efímeras** (dropdowns, modales).
4.  **El Contrato de Fragmentos:** Todo componente dinámico debe poder renderizarse de forma aislada. HTMX solicita estos fragmentos vía REST API y hace `hx-swap`.
5.  **Caddy & WP Rewrites:** El servidor Caddy debe configurar el enrutamiento de WordPress (`try_files {path} {path}/ /index.php?{query}`) para evitar errores 404.

## **⚡ Comandos de Desarrollo & Despliegue**

| Tarea | Comando |
| :--- | :--- |
| Levantar Entorno | `ddev start` |
| Iniciar modo Dev | `ddev exec bun run dev` |
| Pruebas & QA | `ddev exec composer analyze` / `bun run test:e2e` |
| Desplegar a Prod | `ddev exec dep deploy production` |

---
*Desarrollado para la era de la web moderna, la soberanía tecnológica y la colaboración eficiente con IA. © 2026 Cristian Rojas (El Arquitecto)*

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

<!-- skilld -->
Before modifying code, check installed skills for relevant guidance.
Use `activate_skill` to load any matching skill before proceeding.
<!-- /skilld -->

<!-- skilld -->
Before modifying code, check installed skills for relevant guidance.
Use `activate_skill` to load any matching skill before proceeding.
<!-- /skilld -->

<!-- skilld -->
Before modifying code, check installed skills for relevant guidance.
Use `activate_skill` to load any matching skill before proceeding.
<!-- /skilld -->

<!-- skilld -->
Before modifying code, check installed skills for relevant guidance.
Use `activate_skill` to load any matching skill before proceeding.
<!-- /skilld -->

<!-- skilld -->
Before modifying code, check installed skills for relevant guidance.
Use `activate_skill` to load any matching skill before proceeding.
<!-- /skilld -->
