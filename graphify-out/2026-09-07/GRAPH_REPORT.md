# Graph Report - .  (2026-06-14)

## Corpus Check
- Corpus is ~13,478 words - fits in a single context window. You may not need a graph.

## Summary
- 146 nodes · 117 edges · 38 communities (19 shown, 19 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 3 edges (avg confidence: 0.92)
- Token cost: 1,000 input · 500 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Composer Core & Dev Deps|Composer Core & Dev Deps]]
- [[_COMMUNITY_NPM Package Basics|NPM Package Basics]]
- [[_COMMUNITY_Frontend Dev Tools|Frontend Dev Tools]]
- [[_COMMUNITY_Core PHP Libraries (Composer)|Core PHP Libraries (Composer)]]
- [[_COMMUNITY_Theme Bootstrap (Setup)|Theme Bootstrap (Setup)]]
- [[_COMMUNITY_DDEV Environment Config|DDEV Environment Config]]
- [[_COMMUNITY_Asset Management (Providers)|Asset Management (Providers)]]
- [[_COMMUNITY_SEO & Schema (Providers)|SEO & Schema (Providers)]]
- [[_COMMUNITY_Composer Config & Optimization|Composer Config & Optimization]]
- [[_COMMUNITY_Contact Form Controller|Contact Form Controller]]
- [[_COMMUNITY_Navigation & Menus (Providers)|Navigation & Menus (Providers)]]
- [[_COMMUNITY_Infrastructure & Services|Infrastructure & Services]]
- [[_COMMUNITY_ACFPage Fields|ACF/Page Fields]]
- [[_COMMUNITY_Theme OptionsSettings|Theme Options/Settings]]
- [[_COMMUNITY_Routing & Dispatching|Routing & Dispatching]]
- [[_COMMUNITY_WP Initialization (DDEV)|WP Initialization (DDEV)]]
- [[_COMMUNITY_AI-First UI Stack (AlpineHTMX)|AI-First UI Stack (Alpine/HTMX)]]
- [[_COMMUNITY_Rojas Stack Architecture|Rojas Stack Architecture]]
- [[_COMMUNITY_Cloudflare Tunnel Service|Cloudflare Tunnel Service]]
- [[_COMMUNITY_Ngrok Tunnel Service|Ngrok Tunnel Service]]
- [[_COMMUNITY_Bun Package Manager (Web Build)|Bun Package Manager (Web Build)]]
- [[_COMMUNITY_Bun Package Manager (Web Image)|Bun Package Manager (Web Image)]]
- [[_COMMUNITY_AI-First Principle|AI-First Principle]]
- [[_COMMUNITY_Corcel ORM|Corcel ORM]]
- [[_COMMUNITY_Architectural Layers|Architectural Layers]]
- [[_COMMUNITY_Upsun Infrastructure|Upsun Infrastructure]]

## God Nodes (most connected - your core abstractions)
1. `require` - 11 edges
2. `scripts` - 7 edges
3. `ThemeSetup` - 6 edges
4. `AssetServiceProvider` - 5 edges
5. `SeoServiceProvider` - 5 edges
6. `require-dev` - 5 edges
7. `WP Modern Theme Blueprint DDEV Project` - 5 edges
8. `ContactController` - 4 edges
9. `MenuServiceProvider` - 4 edges
10. `config` - 4 edges

## Surprising Connections (you probably didn't know these)
- `Valkey Caching Service` --semantically_similar_to--> `XHGui Profiling Service`  [INFERRED] [semantically similar]
  .ddev/docker-compose.valkey.yaml → .ddev/.ddev-docker-compose-base.yaml
- `Custom DB Commands Environment` --references--> `WP Modern Theme Blueprint DDEV Project`  [EXTRACTED]
  .ddev/commands/db/README.txt → .ddev/config.yaml
- `Rojas Stack Architecture` --rationale_for--> `Phantom WP Core Volume`  [INFERRED]
  .ddev/config.yaml → .ddev/docker-compose.wpcore.yaml
- `Rojas Stack Architecture` --rationale_for--> `Valkey Caching Service`  [EXTRACTED]
  .ddev/config.yaml → .ddev/docker-compose.valkey.yaml

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Rojas Stack Infrastructure Components** — ddev_docker_compose_valkey_valkey_service, ddev_docker_compose_wpcore_phantom_core, ddev_config_rojas_stack [INFERRED 0.95]

## Communities (38 total, 19 thin omitted)

### Community 0 - "Composer Core & Dev Deps"
Cohesion: 0.12
Nodes (16): authors, autoload, files, psr-4, description, license, minimum-stability, name (+8 more)

### Community 1 - "NPM Package Basics"
Cohesion: 0.14
Nodes (13): dependencies, lucide-static, name, private, scripts, build, dev, fix (+5 more)

### Community 2 - "Frontend Dev Tools"
Cohesion: 0.14
Nodes (14): devDependencies, alpinejs, autoprefixer, @biomejs/biome, flowbite, htmx.org, @playwright/test, postcss (+6 more)

### Community 3 - "Core PHP Libraries (Composer)"
Cohesion: 0.18
Nodes (11): require, htmlburger/carbon-fields, illuminate/http, jgrossi/corcel, nesbot/carbon, php, rakit/validation, resend/resend-php (+3 more)

### Community 5 - "DDEV Environment Config"
Cohesion: 0.33
Nodes (6): Custom DB Commands Environment, Nginx-FPM Webserver, PHP 8.5 Runtime, Post-Start Lifecycle Hooks, WP Modern Theme Blueprint DDEV Project, Vite Dev Port (5173)

### Community 8 - "Composer Config & Optimization"
Cohesion: 0.40
Nodes (5): composer/installers, config, allow-plugins, optimize-autoloader, sort-packages

### Community 11 - "Infrastructure & Services"
Cohesion: 0.50
Nodes (4): Rojas Stack Architecture, XHGui Profiling Service, Valkey Caching Service, Phantom WP Core Volume

## Knowledge Gaps
- **67 isolated node(s):** `install-bun.sh script`, `init-wp.sh script`, `cloudflared.sh script`, `ngrok.sh script`, `install-bun.sh script` (+62 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **19 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `require` connect `Core PHP Libraries (Composer)` to `Composer Core & Dev Deps`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Why does `devDependencies` connect `Frontend Dev Tools` to `NPM Package Basics`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **What connects `install-bun.sh script`, `init-wp.sh script`, `cloudflared.sh script` to the rest of the system?**
  _70 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Composer Core & Dev Deps` be split into smaller, more focused modules?**
  _Cohesion score 0.11764705882352941 - nodes in this community are weakly interconnected._
- **Should `NPM Package Basics` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `Frontend Dev Tools` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._