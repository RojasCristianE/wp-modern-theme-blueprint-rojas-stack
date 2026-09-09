# Graph Report - wp-modern-theme-blueprint-rojas-stack  (2026-09-09)

## Corpus Check
- 224 files · ~495,557 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2954 nodes · 3119 edges · 221 communities (184 shown, 26 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 22 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `68cffde2`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- composer.json
- Componentes Twig y tokens de diseño
- Complex Authentication Flow Patterns
- Especificación del Rojas Starter Theme
- Node.js Best Practices
- WP Modern Theme Blueprint DDEV Project
- tooling
- Debugging & Troubleshooting
- Next.js Testing Patterns
- properties
- Node.js Backend Patterns
- Rojas Stack Architecture
- SEO optimization
- Performance & Parallelization
- Component Testing
- Debugging and Managing Flaky Tests
- GitLab CI/CD Configuration
- init-wp.sh
- CI/CD Integration
- Electron Testing
- Browser Extension Testing
- Internationalization (i18n) Testing
- Examples
- Test Annotations & Organization
- Global Setup & Teardown
- devDependencies
- iFrame Testing
- Service Worker Testing
- Fixtures & Hooks
- Environment Variables
- Projects & Dependencies
- Test Reports & Artifacts
- Glob Import
- Accessibility Testing
- Canvas & WebGL Testing
- Test Tags
- Bun Skill Reference
- Patterns
- Date, Time & Clock Mocking
- Security Testing Basics
- Mobile & Responsive Testing
- Advanced Network Interception
- Browser APIs: Geolocation, Permissions & More
- Assertions & Waiting
- Playwright Configuration
- Error & Edge Case Testing
- Test Coverage
- File Upload and Download Testing
- Test Suite Structure
- File Upload & Download Testing
- Patterns
- WebSocket & Real-Time Testing
- Locator Strategies
- Browser Console & JavaScript Error Handling
- Multi-Tab, Window & Popup Testing
- Test Data Factories & Generators
- Vue and Nuxt Testing
- Performance Testing & Web Vitals
- Third-Party Service Mocking
- CI: CircleCI, Azure DevOps, and Jenkins
- Patterns
- Multi-User & Collaboration Testing
- Angular Testing with Playwright
- Patterns
- GitHub Actions for Playwright
- IconService
- ThemeSetup.php
- Patterns
- Tailwind CSS Development Patterns
- Procedure
- detect_wp_project.mjs
- Mocking Strategy: Real vs Mock Services
- Page Object Model (POM)
- Tailwind CSS Layout Patterns
- Tailwind CSS Documentation
- biome.json
- Catálogo
- Procedure
- WP-CLI and Ops
- Queue System
- Laravel Specialist
- Activity-Based Reference Guide
- Drag and Drop Testing
- Procedure
- Procedure
- Livewire Components
- PHP Pro
- Organizing Reusable Test Code
- What's Inside
- Patterns
- Tailwind CSS Accessibility Guidelines
- Tailwind CSS Configuration
- JavaScript API
- Procedure
- **🚀 Rojas Stack: Modern Theme Architecture for WordPress (AI-First)**
- Routing & API Resources
- Choosing Test Types: E2E, Component, or API
- Vite Configuration
- vite/SKILL.md
- Vite Plugin API
- Testing
- Async PHP Patterns
- Symfony Patterns
- Rolldown Migration (Vite 8)
- gutenberg
- WCAG 2.2 Quick Reference
- Modern PHP 8.3+ Features
- Testing & Quality Assurance
- Dark Mode
- Vite Features
- project
- Accessibility (a11y)
- Eloquent ORM
- Laravel Patterns
- Tailwind CSS Animations & Transitions
- tailwind-css-patterns/SKILL.md
- Tailwind CSS Performance Optimization
- tool
- PageFields
- Accessibility Code Patterns
- Operable
- Environment API (Vite 6+)
- Common ARIA patterns
- Understandable
- WordPress Router
- list_blocks.mjs
- detect_block_themes.mjs
- notes
- core
- properties
- Routes and Endpoints (summary)
- **🚀 Rojas Stack — Starter Theme WordPress Moderno (AI-First)**
- Debugging quick routes
- detect_plugins.mjs
- triage.schema.json
- WP Project Triage
- Responses and Fields (summary)
- BlogController
- Arquitectura del Rojas Stack
- WP_Error
- `block.json` (metadata) guidance
- perf_inspect.mjs
- Custom Content Types (summary)
- Schema and Argument Validation (summary)
- Safe `wp search-replace`
- rs_string
- .__construct
- Router decision tree (v1)
- Creating new blocks (scaffolding)
- Dynamic blocks (server rendering)
- Inner Blocks (nested blocks)
- Registration patterns (PHP-first)
- Creating a new block theme
- Debugging block theme issues
- `theme.json` guidance
- Query Monitor (headless / backend-only)
- Data storage, cron, and upgrades
- Debugging quick routes
- Authentication (summary)
- Discovery and Global Parameters (summary)
- Automation with WP-CLI
- Cron, caches, and rewrites
- Debugging WP-CLI
- Multisite targeting
- Safety rules (WP-CLI)
- MenuServiceProvider
- Patterns
- Perceivable
- Common issues by impact
- Attributes and serialization
- Deprecations and migrations
- Tooling and testing
- Patterns (filesystem patterns)
- Autoloaded options
- Object caching
- WP-CLI doctor (`wp doctor`)
- WP-CLI profiling (`wp profile`)
- Activation, deactivation, uninstall
- Security guardrails (plugin work)
- Plugin/theme operations
- wpcli_inspect.mjs
- frontend-design/SKILL.md
- Supports and wrapper attributes
- Templates and template parts
- Plugin structure and loading
- GENERATION.md
- style-variations.md
- cron.md
- database.md
- http-api.md
- measurement.md
- server-timing.md
- settings-api.md
- install-bun.sh
- package.json
- @playwright/test
- scripts
- ArchiveController
- Container-Based Testing
- SeoServiceProvider
- **🛠 Stack Tecnológico (The Dream Team)**
- 4. Flujo de datos
- SearchEngineInterface
- ExampleCptServiceProvider
- MeilisearchIndexer
- MeilisearchClient
- main.js
- seed-demo-content.sh
- sync-icons.sh
- meili-reindex.sh

## God Nodes (most connected - your core abstractions)
1. `Examples` - 24 edges
2. `Tailwind CSS Documentation` - 17 edges
3. `Angular Testing with Playwright` - 16 edges
4. `main()` - 15 edges
5. `rs_string()` - 15 edges
6. `Especificación del Rojas Starter Theme` - 15 edges
7. `Queue System` - 14 edges
8. `Node.js Best Practices` - 14 edges
9. `Next.js Testing Patterns` - 14 edges
10. `MeilisearchClient` - 13 edges

## Surprising Connections (you probably didn't know these)
- `MeilisearchClient` --implements--> `SearchEngineInterface`  [EXTRACTED]
  app/Services/MeilisearchClient.php → app/Contracts/SearchEngineInterface.php
- `Rojas Stack Architecture` --rationale_for--> `Phantom WP Core Volume`  [INFERRED]
  .ddev/config.yaml → .ddev/docker-compose.wpcore.yaml
- `SearchController` --references--> `SearchEngineInterface`  [EXTRACTED]
  app/Controllers/SearchController.php → app/Contracts/SearchEngineInterface.php
- `Rojas Stack Architecture` --rationale_for--> `Valkey Caching Service`  [EXTRACTED]
  .ddev/config.yaml → .ddev/docker-compose.valkey.yaml

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Rojas Stack Infrastructure Components** — ddev_docker_compose_valkey_valkey_service, ddev_docker_compose_wpcore_phantom_core, ddev_config_rojas_stack [INFERRED 0.95]

## Communities (221 total, 26 thin omitted)

### Community 0 - "composer.json"
Cohesion: 0.05
Nodes (38): composer/installers, pestphp/pest-plugin, authors, autoload, files, psr-4, config, allow-plugins (+30 more)

### Community 1 - "Componentes Twig y tokens de diseño"
Cohesion: 0.33
Nodes (6): Componentes de layout (no del catálogo), Componentes Twig y tokens de diseño, Convenciones, daisyUI 5 (2026-09-09), Extender el catálogo, Tokens semánticos

### Community 2 - "Complex Authentication Flow Patterns"
Cohesion: 0.10
Nodes (21): Capturing Verification Tokens, Complete Reset Flow, Complex Authentication Flow Patterns, Detecting Expired Sessions, Email Verification Flows, Expired Token Handling, Fully Mocked Verification, Logout from All Devices (+13 more)

### Community 3 - "Especificación del Rojas Starter Theme"
Cohesion: 0.05
Nodes (43): 10. Plan de documentación, 11.1 Distribución, 11.2 Licencia, 11. Distribución y licenciamiento, 12.1 Desviaciones de la implementación respecto a este plan, 12. Ruta de migración desde el blueprint actual, 13. Preguntas abiertas y supuestos, 14. Criterios de éxito (+35 more)

### Community 4 - "Node.js Best Practices"
Cohesion: 0.05
Nodes (39): 10. Decision Checklist, 1. Framework Selection (2025), 2. Runtime Considerations (2025), 3. Architecture Principles, 4. Error Handling Principles, 5. Async Patterns Principles, 6. Validation Principles, 7. Security Principles (+31 more)

### Community 5 - "WP Modern Theme Blueprint DDEV Project"
Cohesion: 0.40
Nodes (5): Nginx-FPM Webserver, PHP 8.5 Runtime, Post-Start Lifecycle Hooks, WP Modern Theme Blueprint DDEV Project, Vite Dev Port (5173)

### Community 6 - "tooling"
Cohesion: 0.05
Nodes (39): type, type, type, type, type, type, type, additionalProperties (+31 more)

### Community 7 - "Debugging & Troubleshooting"
Cohesion: 0.05
Nodes (37): By Symptom, Capture Browser Console, CI-Specific Configuration, Common Issues, Compare Visual State, Custom Test Attachments, Debug CI Environment, Debug in Code (+29 more)

### Community 8 - "Next.js Testing Patterns"
Cohesion: 0.05
Nodes (37): Anti-Patterns, API Routes, API Through UI, App Router Patterns, Auth Redirects, Auth Setup, Authenticated Tests, Catch-All Routes (+29 more)

### Community 9 - "properties"
Cohesion: 0.06
Nodes (36): additionalProperties, type, additionalProperties, type, additionalProperties, type, additionalProperties, properties (+28 more)

### Community 10 - "Node.js Backend Patterns"
Cohesion: 0.06
Nodes (33): API Response Format, Authentication & Authorization, Caching Strategies, Database Patterns, Dependency Injection, DI Container, JWT Authentication, MongoDB with Mongoose (+25 more)

### Community 11 - "Rojas Stack Architecture"
Cohesion: 0.67
Nodes (3): Rojas Stack Architecture, Valkey Caching Service, Phantom WP Core Volume

### Community 12 - "SEO optimization"
Cohesion: 0.06
Nodes (34): Article, Breadcrumbs, Crawlability, Critical, FAQ, Font sizes, Heading structure, High priority (+26 more)

### Community 13 - "Performance & Parallelization"
Cohesion: 0.06
Nodes (34): Avoiding state leak in parallel runs, Basic Sharding, Benchmarking, Block Resource Types, Block Unnecessary Resources, Browser Contexts, Cache API Responses, CI Sharding Pattern (+26 more)

### Community 14 - "Component Testing"
Cohesion: 0.06
Nodes (34): Anti-Patterns to Avoid, Basic Mount, Component Testing, Configuration, Events & Interactions, Framework-Specific Patterns, Installation, Mocking API Calls (+26 more)

### Community 15 - "Debugging and Managing Flaky Tests"
Cohesion: 0.06
Nodes (33): Annotation-Based Quarantine, Anti-Patterns to Avoid, Async/Timing Flakiness, Categories of Flakiness, CI-Specific Flakiness, Confirming Flakiness, Consistent Viewport and Scale, Data/Parallelism-Driven Flakiness (+25 more)

### Community 16 - "GitLab CI/CD Configuration"
Cohesion: 0.11
Nodes (18): Anti-Patterns, Basic Pipeline Configuration, Browser launch fails: "Failed to launch browser", Decision Guide, Environment Variables and Secrets, GitLab CI/CD Configuration, Key Commands, Merged report is empty after sharded run (+10 more)

### Community 18 - "CI/CD Integration"
Cohesion: 0.06
Nodes (32): Basic Workflow, Best Practices, Cache Node Modules, Cache Playwright Browsers, Caching, CI/CD Integration, CI Configuration Reference, CI-Specific Reporter (+24 more)

### Community 19 - "Electron Testing"
Cohesion: 0.06
Nodes (32): Access Electron APIs, Access Node.js in Renderer, Anti-Patterns to Avoid, Basic Configuration, BrowserWindow Properties, Clipboard, Context Isolation Testing, Development Mode (+24 more)

### Community 20 - "Browser Extension Testing"
Cohesion: 0.07
Nodes (29): Alarms and Timers, Anti-Patterns to Avoid, Background Script Testing, Basic Configuration, Browser Extension Testing, Content Script Communication, Content Script Testing, Context Menus (+21 more)

### Community 21 - "Internationalization (i18n) Testing"
Cohesion: 0.07
Nodes (29): Accept-Language Header, Anti-Patterns to Avoid, Checking for Missing Translations, Critical Element Screenshots, Date, Time & Number Formats, Detecting Text Overflow, Font Loading for i18n, Internationalization (i18n) Testing (+21 more)

### Community 22 - "Examples"
Cohesion: 0.07
Nodes (27): API Resources, Caching, Configuration and Environments, Controllers -> Services -> Actions, Custom Casts and Value Objects, Eager Loading to Avoid N+1, Eloquent Model Patterns, Events, Jobs, and Queues (+19 more)

### Community 23 - "Test Annotations & Organization"
Cohesion: 0.07
Nodes (28): Add Annotations, Annotation Fixture, Annotation Helper, Anti-Patterns to Avoid, Basic Skip, Basic Steps, Conditional Annotations, Conditional Skip (+20 more)

### Community 24 - "Global Setup & Teardown"
Cohesion: 0.07
Nodes (28): Access Config in Global Setup, Anti-Patterns to Avoid, Basic Global Setup, Basic Global Teardown, Combining Both, Common Parallel Pitfall, Conditional Teardown, Configure Global Setup (+20 more)

### Community 25 - "devDependencies"
Cohesion: 0.12
Nodes (17): devDependencies, alpinejs, @biomejs/biome, daisyui, htmx.org, @kucrut/vite-for-wp, lefthook, line-md (+9 more)

### Community 26 - "iFrame Testing"
Cohesion: 0.07
Nodes (27): Accessing Cross-Origin Content, Accessing Nested Frames, Anti-Patterns to Avoid, Basic iFrame Access, Common Patterns, Cross-Origin iFrames, Debugging iFrame Issues, Dynamic iFrames (+19 more)

### Community 27 - "Service Worker Testing"
Cohesion: 0.07
Nodes (27): Anti-Patterns to Avoid, Background Sync, Cache Testing, Getting Service Worker State, Mocking Push Subscription, Offline Testing, Push Notifications, Registration & Lifecycle (+19 more)

### Community 28 - "Fixtures & Hooks"
Cohesion: 0.07
Nodes (27): Anti-Patterns to Avoid, Auth Fixture, Authentication Patterns, Automatic Fixtures, Basic Custom Fixture, beforeAll / afterAll, beforeEach / afterEach, Built-in Fixtures (+19 more)

### Community 31 - "Environment Variables"
Cohesion: 0.33
Nodes (6): Built-in Constants, Custom Variables, Environment Variables, HTML Replacement, Mode-specific Files, TypeScript Support

### Community 32 - "Projects & Dependencies"
Cohesion: 0.07
Nodes (27): Advanced Patterns, Anti-Patterns to Avoid, Authentication Setup, Base Configuration, Basic Multi-Browser Setup, Chained Dependencies, Cleanup Setup, Conditional Projects (+19 more)

### Community 33 - "Test Reports & Artifacts"
Cohesion: 0.07
Nodes (27): Anti-Patterns, Artifact Directory Structure, CI Artifact Upload, CLI Commands, Custom Reporter, Decision Guide, Empty HTML Report, Empty Merged Report (+19 more)

### Community 36 - "Glob Import"
Cohesion: 0.33
Nodes (6): Custom Queries, Eager Loading, Glob Import, Multiple Patterns, Named Imports, Negative Patterns

### Community 37 - "Accessibility Testing"
Cohesion: 0.07
Nodes (27): A11y as CI Gate, A11y Fixture, Accessibility Testing, Anti-Patterns to Avoid, ARIA States, ARIA Validation, Axe-Core Integration, Basic A11y Test (+19 more)

### Community 38 - "Canvas & WebGL Testing"
Cohesion: 0.07
Nodes (27): Anti-Patterns to Avoid, Canvas Basics, Canvas Screenshot Testing, Canvas & WebGL Testing, Chart.js Testing, Chart Libraries, Checking WebGL Support, Click on Canvas (+19 more)

### Community 39 - "Test Tags"
Cohesion: 0.08
Nodes (26): Anti-Patterns to Avoid, Basic Tagging, By Feature Area, By Priority, By Test Type, Combine Group and Test Tags, Common Tag Categories, Complex Patterns (+18 more)

### Community 40 - "Bun Skill Reference"
Cohesion: 0.08
Nodes (24): 1. Initialize a Project, 2. Install Dependencies, 3. Add Packages, 4. Write and Run Code, 5. Create HTTP Server, 6. Write Tests, 7. Bundle for Production, 8. Create Standalone Executable (+16 more)

### Community 41 - "Patterns"
Cohesion: 0.08
Nodes (25): Anti-Patterns, API-Based Login, Authentication Testing, Decision Guide, Different browsers get different cookies, Global Setup Authentication, Global setup fails with "Target page, context or browser has been closed", Login Page Object (+17 more)

### Community 42 - "Date, Time & Clock Mocking"
Cohesion: 0.08
Nodes (25): Advance Time Manually, Always Install Clock Before Navigation, Anti-Patterns to Avoid, Best Practices, Clock API Basics, Clock with Fixture, Date, Time & Clock Mocking, Fixed Time Testing (+17 more)

### Community 43 - "Security Testing Basics"
Cohesion: 0.08
Nodes (25): Anti-Patterns to Avoid, Authentication Security, Authorization Testing, CSRF Protection, Input Validation, Monitor for XSS Execution, Related References, Security Headers (+17 more)

### Community 44 - "Mobile & Responsive Testing"
Cohesion: 0.08
Nodes (24): Anti-Patterns to Avoid, Bottom Sheet, Custom Device Configuration, Device Emulation, Dynamic Viewport Changes, Hamburger Menu, Long Press, Mobile & Responsive Testing (+16 more)

### Community 45 - "Advanced Network Interception"
Cohesion: 0.08
Nodes (24): Advanced Network Interception, Anti-Patterns to Avoid, Conditional Mocking, GraphQL Mock Fixture, GraphQL Mocking, HAR Recording & Playback, HAR with Fallback, Mock Based on Request Body (+16 more)

### Community 46 - "Browser APIs: Geolocation, Permissions & More"
Cohesion: 0.08
Nodes (24): Anti-Patterns to Avoid, Browser APIs: Geolocation, Permissions & More, Camera & Microphone, Clipboard, Clipboard Fixture, Geolocation, Geolocation Fixture, Grant Permissions (+16 more)

### Community 47 - "Assertions & Waiting"
Cohesion: 0.08
Nodes (23): Anti-Patterns to Avoid, Assertions & Waiting, Auto-Waiting (Default), Best Practices, Configure Timeouts, Custom Matchers, Generic Assertions, Locator Assertions (+15 more)

### Community 48 - "Playwright Configuration"
Cohesion: 0.08
Nodes (24): Anti-Patterns, Artifact Collection Strategy, baseURL Not Working, CLI Quick Reference, Decision Guide, Environment-Specific Configuration, Environment Variables with .env, globalSetup / globalTeardown (+16 more)

### Community 49 - "Error & Edge Case Testing"
Cohesion: 0.08
Nodes (24): Anti-Patterns to Avoid, Error Boundaries, Error & Edge Case Testing, Form Validation, Go Offline During Session, Loading States, Network Failures, Offline Testing (+16 more)

### Community 50 - "Test Coverage"
Cohesion: 0.08
Nodes (24): Advanced Patterns, Anti-Patterns to Avoid, Basic Configuration, CI Integration, Collecting Coverage, Converting to Istanbul Format, Coverage for Specific Files, Coverage Reports (+16 more)

### Community 51 - "File Upload and Download Testing"
Cohesion: 0.08
Nodes (24): Authenticated Downloads, Capturing Downloads and Verifying Content, Checking Response Headers, Clearing Selection, Downloading Files, Drag-and-Drop Zones, Enforcing File Count Limits, Enforcing Size Limits (+16 more)

### Community 52 - "Test Suite Structure"
Cohesion: 0.09
Nodes (23): Anti-Patterns to Avoid, API Mocking Patterns, API Tests, Basic Visual Test, Best Practices, Component Tests, Configuration, Directory Structure (+15 more)

### Community 53 - "File Upload & Download Testing"
Cohesion: 0.09
Nodes (23): Anti-Patterns to Avoid, Basic Download, Basic Upload, Clear and Re-upload, Download Fixture, Download with Custom Path, Drag and Drop, Drag and Drop Upload (+15 more)

### Community 54 - "Patterns"
Cohesion: 0.09
Nodes (22): Animations cause random diff failures, Anti-Patterns, CI Configuration, Component Visual Testing, Configuring Thresholds, Cross-Browser Visual Testing, Decision Guide, Disabling Animations (+14 more)

### Community 55 - "WebSocket & Real-Time Testing"
Cohesion: 0.09
Nodes (22): Anti-Patterns to Avoid, Capture Sent Messages, Collaborative Editing, Inject Messages via Page Evaluate, Live Data Updates, Live Notifications, Mock WebSocket with Route Handler, Mocking WebSocket Messages (+14 more)

### Community 56 - "Locator Strategies"
Cohesion: 0.09
Nodes (20): Anti-Patterns to Avoid, Chaining, Common Issues & Solutions, Debugging Locators, Dynamic Content, Filtering & Chaining, getByLabel, getByPlaceholder (+12 more)

### Community 57 - "Browser Console & JavaScript Error Handling"
Cohesion: 0.09
Nodes (22): Anti-Patterns to Avoid, Attach Console to Report, Auto-Fail Fixture, Basic Console Capture, Browser Console & JavaScript Error Handling, Capture by Type, Capture Deprecation Warnings, Capture Error Details (+14 more)

### Community 59 - "Multi-Tab, Window & Popup Testing"
Cohesion: 0.10
Nodes (21): Anti-Patterns to Avoid, Basic Popup, Close All Tabs Except One, Different Users in Different Windows, Google OAuth Popup, Handle Blocked Popups, Intercept New Tab, Link Opens in New Tab (+13 more)

### Community 60 - "Test Data Factories & Generators"
Cohesion: 0.10
Nodes (21): Anti-Patterns to Avoid, API-Based Seeding, Basic Factory, CSV/JSON Data Source, Data-Driven Testing, Database Seeding, Factory Pattern, Factory with Relationships (+13 more)

### Community 61 - "Vue and Nuxt Testing"
Cohesion: 0.10
Nodes (20): Anti-Patterns, Capturing Vue Warnings, Commands, Component Testing, Component Testing Dependencies, Component Testing with Experimental CT, Composition API Components, Configuration (+12 more)

### Community 62 - "Performance Testing & Web Vitals"
Cohesion: 0.10
Nodes (21): Anti-Patterns to Avoid, Budget Fixture, CI Performance Monitoring, Core Web Vitals, Define Budgets, Lighthouse Integration, Lighthouse with Config, Measure LCP, FID, CLS (+13 more)

### Community 63 - "Third-Party Service Mocking"
Cohesion: 0.10
Nodes (20): Analytics & Tracking, Anti-Patterns to Avoid, Block Analytics in Tests, Email Verification, Mock Analytics for Verification, Mock Email API, Mock Google OAuth, Mock PayPal (+12 more)

### Community 64 - "CI: CircleCI, Azure DevOps, and Jenkins"
Cohesion: 0.10
Nodes (20): Anti-Patterns, Azure DevOps, Azure DevOps: Test results not showing, Basic Pipeline, Basic Pipeline, CI: CircleCI, Azure DevOps, and Jenkins, CircleCI, CircleCI: "Executable doesn't exist" (+12 more)

### Community 65 - "Patterns"
Cohesion: 0.10
Nodes (20): Anti-Patterns, Auto-Complete and Typeahead Fields, Date and Time Inputs, Date picker does not accept `fill()` value, Decision Guide, Dynamic Forms — Conditional Fields, `fill()` does nothing or clears but doesn't type, Filling Basic Form Fields (+12 more)

### Community 66 - "Multi-User & Collaboration Testing"
Cohesion: 0.11
Nodes (19): Anti-Patterns to Avoid, Chat & Messaging, Collaborative Document, Concurrent Actions, Cursor Presence, Multi-User & Collaboration Testing, Multi-User Fixture, Multiple Browser Contexts (+11 more)

### Community 67 - "Angular Testing with Playwright"
Cohesion: 0.11
Nodes (19): Angular Material Components, Angular Testing with Playwright, Anti-Patterns, Build Configurations, CDK Overlay Container, Configuration, Lazy-Loaded Modules, Locator Strategies (+11 more)

### Community 68 - "Patterns"
Cohesion: 0.11
Nodes (19): Anti-Patterns, Component Testing (Experimental), CRA vs Vite Differences, Detecting Memory Leaks, E2E Config (Vite), Error Boundaries, Form Libraries (React Hook Form, Formik), Framework Tips (+11 more)

### Community 69 - "GitHub Actions for Playwright"
Cohesion: 0.11
Nodes (19): Basic Workflow, Browser launch fails: "Missing dependencies", CLI Commands, Common Mistakes, Container-Based Execution, Environment Secrets, GitHub Actions for Playwright, No PR annotations (+11 more)

### Community 70 - "IconService"
Cohesion: 0.18
Nodes (3): IconServiceProvider, IconService, Timber\Twig

### Community 71 - "ThemeSetup.php"
Cohesion: 0.20
Nodes (3): RouteServiceProvider, Closure, WP_Query

### Community 72 - "Patterns"
Cohesion: 0.11
Nodes (19): "401 Unauthorized" when using `request` fixture, Anti-Patterns, API Data Seeding, API Testing, Chained API Calls, CRUD Operations, Decision Guide, Dedicated API Project Configuration (+11 more)

### Community 73 - "Tailwind CSS Development Patterns"
Cohesion: 0.11
Nodes (19): Best Practices, Classes Not Applying, Common Patterns, Constraints and Warnings, Dark Mode Issues, Dark Mode Toggle, Examples, External Resources (+11 more)

### Community 74 - "Procedure"
Cohesion: 0.11
Nodes (18): 0) Triage and locate blocks, 10) Tooling and verification commands, 1) Create a new block (if needed), 2) Ensure apiVersion 3 (WordPress 6.9+), 3) Pick the right block model, 4) Update `block.json` safely, 5) Register the block (server-side preferred), 6) Implement edit/save/render patterns (+10 more)

### Community 75 - "detect_wp_project.mjs"
Cohesion: 0.29
Nodes (18): buildRecommendations(), DEFAULT_IGNORES, detectConfigConstants(), detectKinds(), detectPackageManager(), detectPluginHeaderFromPhpFile(), detectThemeHeaderFromStyleCss(), existsDir() (+10 more)

### Community 76 - "Mocking Strategy: Real vs Mock Services"
Cohesion: 0.11
Nodes (18): Anti-Patterns, Blocking Unwanted Requests, Core Principle, Decision Flowchart, Decision Matrix, Environment-Based Test Projects, Full Mock (route.fulfill), Hybrid Approach: Fixture-Based Mock Control (+10 more)

### Community 77 - "Page Object Model (POM)"
Cohesion: 0.12
Nodes (17): Basic Structure, Best Practices, Component Objects, Composition Patterns, Directory Structure, Do, Don't, Factory Functions (+9 more)

### Community 78 - "Tailwind CSS Layout Patterns"
Cohesion: 0.12
Nodes (16): Background Colors, Colors, Container & Max Width, Flexbox Layouts, Font Size & Weight, Grid Layouts, Layout Utilities, Line Height & Letter Spacing (+8 more)

### Community 79 - "Tailwind CSS Documentation"
Cohesion: 0.12
Nodes (17): Applying Variants in CSS, Arbitrary Values, Color System, Custom Theme Configuration, Custom Utilities, Custom Variants, Dark Mode, Functions and Directives (+9 more)

### Community 80 - "biome.json"
Cohesion: 0.12
Nodes (16): source, assist, actions, css, parser, files, includes, formatter (+8 more)

### Community 81 - "Catálogo"
Cohesion: 0.25
Nodes (8): `alert.twig`, `badge.twig`, `button.twig`, `card.twig`, Catálogo, `icon.twig`, `input.twig`, `pagination.twig`

### Community 82 - "Procedure"
Cohesion: 0.12
Nodes (15): 0) Guardrails: measure first, avoid risky ops, 1) Generate a backend-only performance report (deterministic), 2) Fast wins: run diagnostics before deep profiling, 3) Deep profiling (no browser required), 4) Query Monitor (backend-only usage), 5) Fix by category (choose the dominant bottleneck), 6) Verify (repeat the same measurement), Escalation (+7 more)

### Community 83 - "WP-CLI and Ops"
Cohesion: 0.12
Nodes (15): 0) Guardrails: confirm environment and blast radius, 1) Inspect WP-CLI and site targeting (deterministic), 2) Choose the right workflow, 3) Automation patterns (scripts + wp-cli.yml), A) Safe URL/domain migration (`search-replace`), B) Plugin/theme operations, C) Cron and queues, D) Multisite operations (+7 more)

### Community 84 - "Queue System"
Cohesion: 0.13
Nodes (14): Best Practices, Dispatching Jobs, Failed Jobs, Horizon Setup, Job Batching, Job Chaining, Job Middleware, Job Patterns (+6 more)

### Community 85 - "Laravel Specialist"
Cohesion: 0.13
Nodes (14): API Resource, Code Templates, Constraints, Core Workflow, Eloquent Model, Feature Test (Pest), Knowledge Reference, Laravel Specialist (+6 more)

### Community 86 - "Activity-Based Reference Guide"
Cohesion: 0.13
Nodes (15): Activity-Based Reference Guide, Advanced Patterns, Architecture Decisions, Debugging & Troubleshooting, Error & Edge Case Testing, Framework-Specific Testing, Infrastructure & Configuration, Mobile & Responsive Testing (+7 more)

### Community 87 - "Drag and Drop Testing"
Cohesion: 0.13
Nodes (14): Canvas Coordinate-Based Dragging, Cross-Frame Dragging, Custom Drag Preview, Drag and Drop Testing, File Drop Zone, Incremental Mouse Movement for Custom Libraries, Kanban Board (Cross-Column Movement), Keyboard-Based Reordering (+6 more)

### Community 88 - "Procedure"
Cohesion: 0.13
Nodes (14): 0) Triage and locate block theme roots, 1) Create a new block theme (if needed), 2) Confirm theme type and override expectations, 3) Make `theme.json` changes safely, 4) Templates and template parts, 5) Patterns, 6) Style variations, Escalation (+6 more)

### Community 89 - "Procedure"
Cohesion: 0.13
Nodes (14): 0) Triage and locate REST usage, 1) Choose the right approach, 2) Register routes safely (namespaces, methods, permissions), 3) Validate/sanitize request args, 4) Responses, fields, and links, 5) Authentication and authorization, 6) Client-facing behavior (discovery, pagination, embeds), Escalation (+6 more)

### Community 90 - "Livewire Components"
Cohesion: 0.14
Nodes (13): Authorization, Blade Template, Component Patterns, Events, Form Component, Form Template, Listen to Browser Events, Livewire Components (+5 more)

### Community 91 - "PHP Pro"
Cohesion: 0.14
Nodes (13): Code Patterns, Constraints, Core Workflow, Enum (PHP 8.1+), Knowledge Reference, MUST DO, MUST NOT DO, Output Templates (+5 more)

### Community 92 - "Organizing Reusable Test Code"
Cohesion: 0.14
Nodes (14): Anti-Patterns, Combined Project Structure, Custom Fixtures, Helper Functions, Helpers with side effects, Locator-only page objects, Monolithic fixtures, Organizing Reusable Test Code (+6 more)

### Community 93 - "What's Inside"
Cohesion: 0.14
Nodes (13): Advanced (`advanced/`), Architecture (`architecture/`), Browser APIs (`browser-apis/`), Core (`core/`), Debugging (`debugging/`), Frameworks (`frameworks/`), Infrastructure & CI/CD (`infrastructure-ci-cd/`), Installation (+5 more)

### Community 94 - "Patterns"
Cohesion: 0.14
Nodes (14): Anti-Patterns, Authenticated GraphQL Fixture, Authorization Errors, Basic Query with Variables, "Cannot query field X on type Y", GraphQL Helper Function, GraphQL returns 200 but data is null, GraphQL Testing (+6 more)

### Community 95 - "Tailwind CSS Accessibility Guidelines"
Cohesion: 0.14
Nodes (13): Accessibility Checklist, Alert Dialog, ARIA Patterns with Tailwind, Color Contrast, Contrast Guidelines, Focus Management, Focus Visible vs Focus, Motion Preferences (+5 more)

### Community 96 - "Tailwind CSS Configuration"
Cohesion: 0.14
Nodes (13): Advanced v4.1 Features, Creating a Reusable Preset, CSS-First Configuration (v4.1+), Custom Plugin Example, Custom Utilities, Enhanced Arbitrary Values, JavaScript Configuration (Legacy), Native CSS Custom Properties (+5 more)

### Community 97 - "JavaScript API"
Cohesion: 0.14
Nodes (13): build, Build and SSR, createServer, JavaScript API, Library Mode, loadEnv, Multi-Page App, Multiple Entries (+5 more)

### Community 98 - "Procedure"
Cohesion: 0.14
Nodes (13): 0) Triage and locate plugin entrypoints, 1) Follow a predictable architecture, 2) Hooks and lifecycle (activation/deactivation/uninstall), 3) Settings and admin UI (Settings API), 4) Security baseline (always), 5) Data storage, cron, migrations (if needed), Escalation, Failure modes / debugging (+5 more)

### Community 99 - "**🚀 Rojas Stack: Modern Theme Architecture for WordPress (AI-First)**"
Cohesion: 0.29
Nodes (7): **🌐 Arquitectura Macro (Infraestructura del Servidor)**, **⚡ Comandos de Desarrollo & Despliegue**, **📂 Estructura del Tema (`app/` y `resources/`)**, **🧠 Filosofía del Stack**, graphify, **🤝 Reglas de Integración Front-Back (Safe Rules)**, **🚀 Rojas Stack: Modern Theme Architecture for WordPress (AI-First)**

### Community 100 - "Routing & API Resources"
Cohesion: 0.15
Nodes (12): API Resources, API Routes, API Versioning, Controllers, CORS Configuration, Form Requests, Middleware, Resource Collections (+4 more)

### Community 101 - "Choosing Test Types: E2E, Component, or API"
Cohesion: 0.15
Nodes (13): API Layer (60% of tests), API Tests, Choosing Test Types: E2E, Component, or API, Common Mistakes, Component Layer (30% of tests), Component Tests, Decision Matrix, E2E Layer (10% of tests) (+5 more)

### Community 102 - "Vite Configuration"
Cohesion: 0.15
Nodes (12): Async Config, Basic Setup, build.target, Conditional Config, define (Global Constants), Key Config Options, plugins, resolve.alias (+4 more)

### Community 103 - "vite/SKILL.md"
Cohesion: 0.15
Nodes (9): Advanced, Build & SSR, CLI Commands, Common Config, Core, Official Plugins, Preferences, Quick Reference (+1 more)

### Community 104 - "Vite Plugin API"
Cohesion: 0.15
Nodes (13): Basic Structure, Client-Server Communication, Conditional Application, config, configResolved, configureServer, handleHotUpdate, Plugin Ordering (+5 more)

### Community 105 - "Testing"
Cohesion: 0.17
Nodes (11): API Testing, Authentication Testing, Best Practices, Database Testing, Factories, Feature Tests, Mocking, Pest PHP (+3 more)

### Community 106 - "Async PHP Patterns"
Cohesion: 0.17
Nodes (11): Amphp Framework, Async PHP Patterns, PHP Fibers (Native PHP 8.1+), Quick Reference, ReactPHP Async MySQL, ReactPHP Event Loop, ReactPHP Promises, Swoole Async MySQL (+3 more)

### Community 107 - "Symfony Patterns"
Cohesion: 0.17
Nodes (11): Console Commands, Controllers with Attributes, Custom Events, Dependency Injection, DTOs with Validation, Event Subscribers, Message Handler (Messenger), Quick Reference (+3 more)

### Community 108 - "Rolldown Migration (Vite 8)"
Cohesion: 0.17
Nodes (12): Config Migration, Custom Transform Targets, esbuild → oxc, Gradual Migration, JSX Configuration, New Capabilities, Overriding Vite in Frameworks, Performance Impact (+4 more)

### Community 109 - "gutenberg"
Cohesion: 0.17
Nodes (12): additionalProperties, properties, type, gutenberg, source, value, versions, type (+4 more)

### Community 110 - "WCAG 2.2 Quick Reference"
Cohesion: 0.18
Nodes (8): Level A (minimum), Level AA (standard), Level AAA (enhanced), Sources, Success criteria by level, Testing tools, WCAG 2.2 Quick Reference, What changed from 2.1 to 2.2

### Community 111 - "Modern PHP 8.3+ Features"
Cohesion: 0.18
Nodes (10): Attributes (Metadata), Enums with Methods, Fibers (PHP 8.1+), First-Class Callables, Match Expressions, Modern PHP 8.3+ Features, Never Type, Quick Reference (+2 more)

### Community 112 - "Testing & Quality Assurance"
Cohesion: 0.18
Nodes (10): Code Coverage, Data Providers, Laravel Feature Tests, Mockery (Advanced Mocking), Pest Testing (Modern Alternative), PHPStan Annotations, PHPStan Configuration, PHPUnit with Strict Types (+2 more)

### Community 113 - "Dark Mode"
Cohesion: 0.18
Nodes (10): Basic Dark Mode Support, Container Queries (v4.1+), Dark Mode, Dark Mode Best Practices, Dark Mode Toggle (React), Mobile-First Responsive Layout, Responsive Card Component, Responsive Design Patterns (+2 more)

### Community 114 - "Vite Features"
Cohesion: 0.18
Nodes (10): Asset Import Queries, CSS Modules, Explicit URL, HMR API, Inline/No-Inline, JSON Import, Raw String, URL Import (+2 more)

### Community 115 - "project"
Cohesion: 0.18
Nodes (11): enum, items, type, type, additionalProperties, properties, required, type (+3 more)

### Community 116 - "Accessibility (a11y)"
Cohesion: 0.20
Nodes (10): Accessibility (a11y), ARIA usage (4.1.2), Automated testing, Conformance levels, Live regions (4.1.3), Manual testing, References, Robust (+2 more)

### Community 117 - "Eloquent ORM"
Cohesion: 0.20
Nodes (9): Advanced Queries, Custom Casts, Eloquent ORM, Model Events, Model Patterns, Performance Tips, Query Optimization, Query Scopes (+1 more)

### Community 118 - "Laravel Patterns"
Cohesion: 0.20
Nodes (9): API Resources, Controllers with DTOs, Event Listeners, Form Requests with Enums, Jobs & Queues, Laravel Patterns, Quick Reference, Repository Pattern (+1 more)

### Community 119 - "Tailwind CSS Animations & Transitions"
Cohesion: 0.20
Nodes (9): Basic Transitions, Built-in Animations, Common Use Cases, Custom Animations (v4.1+), Global Reduced Motion Support, Motion Preferences, Tailwind CSS Animations & Transitions, Transform Effects (+1 more)

### Community 120 - "tailwind-css-patterns/SKILL.md"
Cohesion: 0.20
Nodes (7): Card Component, Form Elements, Modal/Dialog, Navigation Bar, React Button Component with Variants, Responsive User Card, Tailwind CSS Component Patterns

### Community 121 - "Tailwind CSS Performance Optimization"
Cohesion: 0.20
Nodes (9): Best Practices for Performance, Bundle Size Optimization, Content Path Best Practices, CSS Optimization Techniques, Development Performance (v4.1+), Minification, Production Build Optimization, PurgeCSS Configuration (+1 more)

### Community 122 - "tool"
Cohesion: 0.20
Nodes (10): const, type, name, tool, version, additionalProperties, properties, required (+2 more)

### Community 123 - "PageFields"
Cohesion: 0.22
Nodes (4): PageFields, ThemeOptions, Carbon_Fields\Container, Carbon_Fields\Field

### Community 124 - "Accessibility Code Patterns"
Cohesion: 0.22
Nodes (9): Accessibility Code Patterns, ARIA tabs, Dragging movements, Error handling, Form labels, Live regions and notifications, Modal focus trap, Screen reader commands (+1 more)

### Community 125 - "Operable"
Cohesion: 0.22
Nodes (9): Dragging movements (2.5.7) — new in 2.2, Focus not obscured (2.4.11) — new in 2.2, Focus visible (2.4.7), Keyboard accessible (2.1), Motion (2.3), Operable, Skip links (2.4.1), Target size (2.5.8) — new in 2.2 (+1 more)

### Community 126 - "Environment API (Vite 6+)"
Cohesion: 0.22
Nodes (9): Backward Compatibility, Basic Configuration, Concept, Custom Environment Instances, Environment API (Vite 6+), Environment Options, Multiple Environments, Plugin Environment Access (+1 more)

### Community 127 - "Common ARIA patterns"
Cohesion: 0.25
Nodes (8): Buttons, Common ARIA patterns, Error states, Form fields, Links, Live regions, Modals, Navigation

### Community 128 - "Understandable"
Cohesion: 0.25
Nodes (8): Accessible authentication (3.3.8) — new in 2.2, Consistent help (3.2.6) — new in 2.2, Consistent navigation (3.2.3), Error handling (3.3.1, 3.3.3), Form labels (3.3.2), Page language (3.1.1), Redundant entry (3.3.7) — new in 2.2, Understandable

### Community 129 - "WordPress Router"
Cohesion: 0.25
Nodes (7): Escalation, Failure modes / debugging, Inputs required, Procedure, Verification, When to use, WordPress Router

### Community 130 - "list_blocks.mjs"
Cohesion: 0.39
Nodes (7): DEFAULT_IGNORES, existsDir(), findFilesRecursive(), main(), readJsonSafe(), statSafe(), summarizeBlockJson()

### Community 131 - "detect_block_themes.mjs"
Cohesion: 0.43
Nodes (7): DEFAULT_IGNORES, existsDir(), findFilesRecursive(), main(), readJsonSafe(), statSafe(), summarizeTheme()

### Community 132 - "notes"
Cohesion: 0.29
Nodes (8): items, type, type, items, type, commands, notes, properties

### Community 133 - "core"
Cohesion: 0.25
Nodes (8): additionalProperties, properties, type, core, wordpress, additionalProperties, properties, type

### Community 134 - "properties"
Cohesion: 0.25
Nodes (8): properties, recommendations, signals, additionalProperties, type, additionalProperties, required, type

### Community 135 - "Routes and Endpoints (summary)"
Cohesion: 0.25
Nodes (7): Arguments, Methods, Namespacing, permission_callback (required), Registering routes, Return values, Routes and Endpoints (summary)

### Community 136 - "**🚀 Rojas Stack — Starter Theme WordPress Moderno (AI-First)**"
Cohesion: 0.14
Nodes (14): **🤖 Agentes de IA**, **✨ Características**, **⚡ Comandos**, **🎨 Componentes**, **🧩 Configuración de módulos**, **🚢 Despliegue (theme-only)**, **Diseñado por Cristian Rojas (El Arquitecto)**, **📂 Estructura** (+6 more)

### Community 137 - "Debugging quick routes"
Cohesion: 0.29
Nodes (6): Attributes not saving, Block doesn’t appear in inserter, Console warnings about apiVersion (WordPress 6.9+), Debugging quick routes, Styles not applying in editor (apiVersion 3 / iframe), “This block contains unexpected or invalid content”

### Community 138 - "detect_plugins.mjs"
Cohesion: 0.43
Nodes (5): DEFAULT_IGNORES, findFilesRecursive(), main(), parsePluginHeader(), readFileSafe()

### Community 139 - "triage.schema.json"
Cohesion: 0.29
Nodes (6): additionalProperties, $id, required, $schema, title, type

### Community 140 - "WP Project Triage"
Cohesion: 0.29
Nodes (6): Failure modes / debugging, Inputs required, Procedure, Verification, When to use, WP Project Triage

### Community 141 - "Responses and Fields (summary)"
Cohesion: 0.29
Nodes (6): Do not remove core fields, Links and embedding, Raw vs rendered content, register_meta / register_post_meta / register_term_meta, register_rest_field, Responses and Fields (summary)

### Community 142 - "BlogController"
Cohesion: 0.14
Nodes (4): BlogController, DesignSystemController, Rakit\Validation\Validator, Timber\Timber

### Community 143 - "Arquitectura del Rojas Stack"
Cohesion: 0.22
Nodes (9): 1. Filosofía, 2. Capas y responsabilidades, 3. Flujo de una petición, 5. Assets (Vite + vite-for-wp), 6. Sistema de diseño, 7. Calidad y testing, 8. Despliegue (theme-only), 9. Decisiones registradas (ADRs breves) (+1 more)

### Community 146 - "`block.json` (metadata) guidance"
Cohesion: 0.33
Nodes (5): API version + schema, `block.json` (metadata) guidance, Helpful upstream references, Modern asset fields to know, Practical rules

### Community 147 - "perf_inspect.mjs"
Cohesion: 0.60
Nodes (5): canRun(), existsFile(), main(), parseArgs(), runWp()

### Community 148 - "Custom Content Types (summary)"
Cohesion: 0.33
Nodes (5): Adding REST support to existing types, Custom Content Types (summary), Custom post types, Custom taxonomies, Discovery links for custom controllers

### Community 149 - "Schema and Argument Validation (summary)"
Cohesion: 0.33
Nodes (5): Formats and types, JSON Schema in WordPress, Schema and Argument Validation (summary), Schema caching, Validation + sanitization

### Community 150 - "Safe `wp search-replace`"
Cohesion: 0.33
Nodes (5): Common flags, Multisite notes, Recommended workflow, Safe `wp search-replace`, Serialization caution

### Community 151 - "rs_string"
Cohesion: 0.24
Nodes (4): FormController, rs_array(), rs_int(), rs_string()

### Community 153 - "Router decision tree (v1)"
Cohesion: 0.40
Nodes (4): Router decision tree (v1), Step 1: classify repo kind (from triage), Step 2: route by user intent (keywords), Step 3: guardrails checklist (always)

### Community 154 - "Creating new blocks (scaffolding)"
Cohesion: 0.40
Nodes (4): Creating new blocks (scaffolding), Manual fallback (when scaffolding is not available), “Most up-to-date” interactive blocks, Preferred path: `@wordpress/create-block`

### Community 155 - "Dynamic blocks (server rendering)"
Cohesion: 0.40
Nodes (4): Choose the mechanism, Dynamic blocks (server rendering), Practical checklist, Wrapper attributes

### Community 156 - "Inner Blocks (nested blocks)"
Cohesion: 0.40
Nodes (4): Canonical references, Common pitfalls, Inner Blocks (nested blocks), Practical patterns

### Community 157 - "Registration patterns (PHP-first)"
Cohesion: 0.40
Nodes (4): Dynamic render mapping, Prefer metadata registration, Registration patterns (PHP-first), Where to register

### Community 158 - "Creating a new block theme"
Cohesion: 0.40
Nodes (4): Creating a new block theme, References, theme.json version choice (compatibility), Two practical starting points

### Community 159 - "Debugging block theme issues"
Cohesion: 0.40
Nodes (4): Debugging block theme issues, Style variations not updating, Styles not applying, Templates/parts not showing

### Community 160 - "`theme.json` guidance"
Cohesion: 0.40
Nodes (4): High-level structure, Practical guardrails, `theme.json` guidance, WordPress 6.9 additions

### Community 161 - "Query Monitor (headless / backend-only)"
Cohesion: 0.40
Nodes (4): Guardrails, Query Monitor (headless / backend-only), REST API requests (no browser needed), What it can show

### Community 162 - "Data storage, cron, and upgrades"
Cohesion: 0.40
Nodes (4): Cron, Data storage, Data storage, cron, and upgrades, Database safety note

### Community 163 - "Debugging quick routes"
Cohesion: 0.40
Nodes (4): Activation hook surprises, Debugging quick routes, Plugin doesn’t load / fatal errors, Settings not saving

### Community 164 - "Authentication (summary)"
Cohesion: 0.40
Nodes (4): Application Passwords (external clients), Auth plugins, Authentication (summary), Cookie authentication (in-dashboard / same-site)

### Community 165 - "Discovery and Global Parameters (summary)"
Cohesion: 0.40
Nodes (4): API discovery, Discovery and Global Parameters (summary), Global parameters, Pagination

### Community 166 - "Automation with WP-CLI"
Cohesion: 0.40
Nodes (4): Automation with WP-CLI, CI jobs, Shell scripting, `wp-cli.yml`

### Community 167 - "Cron, caches, and rewrites"
Cohesion: 0.40
Nodes (4): Cache + rewrite, Cron, Cron, caches, and rewrites, Guardrails

### Community 168 - "Debugging WP-CLI"
Cohesion: 0.40
Nodes (4): Debugging WP-CLI, HTTP/URL targeting issues, Permission/file ownership issues, WP not found / wrong WP root

### Community 169 - "Multisite targeting"
Cohesion: 0.40
Nodes (4): Common commands, Guardrails, Key flags, Multisite targeting

### Community 170 - "Safety rules (WP-CLI)"
Cohesion: 0.40
Nodes (4): Golden rules, High-risk commands (require explicit confirmation), Logging, Safety rules (WP-CLI)

### Community 173 - "Patterns"
Cohesion: 0.11
Nodes (18): Anti-Patterns, CLI Commands, Decision Guide, Dynamic Shard Count, Merged report missing results, Merging Shard Reports, More workers = Slower, "No tests found" in some shards (+10 more)

### Community 174 - "Perceivable"
Cohesion: 0.50
Nodes (4): Color contrast (1.4.3, 1.4.6), Media alternatives (1.2), Perceivable, Text alternatives (1.1)

### Community 175 - "Common issues by impact"
Cohesion: 0.50
Nodes (4): Common issues by impact, Critical (fix immediately), Moderate (fix soon), Serious (fix before launch)

### Community 176 - "Attributes and serialization"
Cohesion: 0.50
Nodes (3): Attributes and serialization, Common pitfalls, How attributes persist

### Community 177 - "Deprecations and migrations"
Cohesion: 0.50
Nodes (3): `deprecated` basics, Deprecations and migrations, Practical guardrails

### Community 178 - "Tooling and testing"
Cohesion: 0.50
Nodes (3): Common toolchains, Tooling and testing, Verification checklist

### Community 179 - "Patterns (filesystem patterns)"
Cohesion: 0.50
Nodes (3): Filesystem patterns, Patterns (filesystem patterns), Practical guardrails

### Community 180 - "Autoloaded options"
Cohesion: 0.50
Nodes (3): Autoloaded options, Fix patterns, Quick checks

### Community 181 - "Object caching"
Cohesion: 0.50
Nodes (3): Concepts, Fix patterns, Object caching

### Community 182 - "WP-CLI doctor (`wp doctor`)"
Cohesion: 0.50
Nodes (3): Install (if missing), Recommended usage, WP-CLI doctor (`wp doctor`)

### Community 183 - "WP-CLI profiling (`wp profile`)"
Cohesion: 0.50
Nodes (3): Install (if missing), Recommended sequence, WP-CLI profiling (`wp profile`)

### Community 184 - "Activation, deactivation, uninstall"
Cohesion: 0.50
Nodes (3): Activation / deactivation hooks, Activation, deactivation, uninstall, Uninstall

### Community 185 - "Security guardrails (plugin work)"
Cohesion: 0.50
Nodes (3): Nonces + permissions, Sanitization and escaping, Security guardrails (plugin work)

### Community 186 - "Plugin/theme operations"
Cohesion: 0.50
Nodes (3): Common commands, Guardrails, Plugin/theme operations

### Community 187 - "wpcli_inspect.mjs"
Cohesion: 0.83
Nodes (3): main(), parseArgs(), runWp()

### Community 204 - "package.json"
Cohesion: 0.12
Nodes (17): name, private, type, version, @biomejs/biome, daisyui, @kucrut/vite-for-wp, lefthook (+9 more)

### Community 206 - "@playwright/test"
Cohesion: 0.18
Nodes (3): envPath, wpEnv, @playwright/test

### Community 207 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, build, dev, fix, icons:sync, lint, prepare, preview (+1 more)

### Community 209 - "Container-Based Testing"
Cohesion: 0.12
Nodes (15): Anti-Patterns, "browserType.launch: Executable doesn't exist", CI Container Jobs, Container-Based Testing, Custom Dockerfile, Decision Guide, Dev Container Setup, Docker Compose Stack (+7 more)

### Community 211 - "**🛠 Stack Tecnológico (The Dream Team)**"
Cohesion: 0.40
Nodes (5): **1. Backend & Data (PHP & Composer)**, **2. Frontend & Interacción (Bun & Vite)**, **3. CI/CD & Despliegue del Tema**, **4. QA & Debugging**, **🛠 Stack Tecnológico (The Dream Team)**

### Community 212 - "4. Flujo de datos"
Cohesion: 0.40
Nodes (5): 4.1 Lectura (Timber / WP_Query), 4.2 Metadatos (code-first), 4.3 Escritura (formularios), 4.4 Lectura (búsqueda en vivo), 4. Flujo de datos

### Community 219 - "sync-icons.sh"
Cohesion: 0.83
Nodes (3): fail(), sync-icons.sh script, sync_set()

## Knowledge Gaps
- **1970 isolated node(s):** `DEFAULT_IGNORES`, `DEFAULT_IGNORES`, `DEFAULT_IGNORES`, `$schema`, `$id` (+1965 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 2126 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **26 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Debugging & Troubleshooting` connect `Debugging & Troubleshooting` to `playwright-best-practices/SKILL.md`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Why does `Debugging and Managing Flaky Tests` connect `Debugging and Managing Flaky Tests` to `playwright-best-practices/SKILL.md`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Why does `Date, Time & Clock Mocking` connect `Date, Time & Clock Mocking` to `playwright-best-practices/SKILL.md`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **What connects `DEFAULT_IGNORES`, `DEFAULT_IGNORES`, `DEFAULT_IGNORES` to the rest of the system?**
  _1970 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._
- **Should `Complex Authentication Flow Patterns` be split into smaller, more focused modules?**
  _Cohesion score 0.09523809523809523 - nodes in this community are weakly interconnected._
- **Should `Especificación del Rojas Starter Theme` be split into smaller, more focused modules?**
  _Cohesion score 0.046511627906976744 - nodes in this community are weakly interconnected._