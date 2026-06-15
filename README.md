# **🚀 Rojas Stack: Modern Theme Architecture for WordPress**

[![Stack](https://img.shields.io/badge/Stack-Monolith_Moderno-6366f1)](https://github.com/cristian-rojas/wp-modern-theme-blueprint)
[![Architecture](https://img.shields.io/badge/Architecture-AI--First-blue)](./GEMINI.md)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

El **Rojas Stack** es un blueprint de ingeniería diseñado para el desarrollo de temas de WordPress de alto rendimiento. Elimina la dependencia de constructores visuales y plugins innecesarios, adoptando un enfoque **Code-First** y **AI-Ready**.

## **🌟 Características Principales**

*   **⚡ Performance Extremo:** Bundling con **Vite**, interactividad con **HTMX/AlpineJS** y caché en el Edge.
*   **🧠 IA-First:** Arquitectura estrictamente tipada y documentada para facilitar la colaboración con agentes de IA.
*   **🛠 Soberanía Tecnológica:** Uso de estándares industriales (**PHP 8.2+**, **Bun**, **Docker**) y herramientas OSS (Valkey, MariaDB).
*   **🔄 CI/CD Profesional:** Despliegues atómicos con **Deployer** y versionado automático con **Semantic Release**.
*   **🛡️ Seguridad Integrada:** Manejo nativo de Nonces para HTMX y políticas de integridad de datos.

## **🛠 Stack Tecnológico**

| Capa | Tecnologías |
| :--- | :--- |
| **Backend** | PHP 8.2+, Composer, Timber (Twig), Corcel (Eloquent), Carbon Fields |
| **Frontend** | Bun, Vite, Tailwind CSS, Flowbite, HTMX, AlpineJS |
| **DevOps** | DDEV, GitHub Actions, Deployer, Semantic Release |
| **Data/Cache** | MariaDB, Valkey (RESP compatible) |
| **QA/Testing** | PHPStan, Biome, Playwright, Spatie Ignition |

## **📂 Estructura del Proyecto**

```text
wp-modern-theme-blueprint/
├── app/                  # Lógica de Negocio (PHP Strict)
│   ├── Contracts/        # Interfaces y Contratos
│   ├── Controllers/      # Gestión de datos para Twig
│   └── Providers/        # Integración con WordPress Hooks
├── resources/            # Assets de Desarrollo
│   ├── scripts/          # JavaScript Moderno
│   └── views/            # Plantillas Twig (.twig)
├── infra/                # Configuración de Servidor (Caddy)
├── deploy.php            # Receta de despliegue (Deployer)
└── GEMINI.md             # Constitución de Ingeniería (LEER PRIMERO)
```

## **🚀 Inicio Rápido**

1.  **Requisitos:** Tener instalado **Docker**, **DDEV**, **Bun** y **Composer**.
2.  **Instalación:**
    ```bash
    ddev start
    composer install
    bun install
    ```
3.  **Desarrollo:**
    ```bash
    bun run dev
    ```

## **🤝 Reglas de Oro**

1.  **Corcel es Read-Only:** La escritura de datos siempre usa funciones nativas de WP.
2.  **Estado Híbrido:** Estado pesado en Servidor (HTMX), micro-interacciones en Cliente (Alpine).
3.  **Cero Plugins de UI:** Todo componente visual se define en el tema, no en la DB.

---

### **Diseñado por Cristian Rojas (El Arquitecto)**
*Optimizado para la era de la web moderna, la soberanía tecnológica y la eficiencia extrema.*
