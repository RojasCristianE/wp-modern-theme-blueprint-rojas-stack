# Componentes Twig y tokens de diseño

> Catálogo de referencia (spec §10). Vista interactiva en vivo: ruta `/__components` (activa solo con `WP_ENV=development`, módulo `design_system` en [`features.php`](../app/Config/features.php)).
> Decisiones de arquitectura: [`ARCHITECTURE.md`](./ARCHITECTURE.md).

## Convenciones

- Ubicación: `resources/views/components/`.
- **API por props:** cada componente recibe todo lo que necesita en el `include` — nada de globals implícitas.
- **Pureza con `only`:** los includes de listado pasan `{% include ... with {...} only %}` para que el componente no herede contexto externo.
- **Tokens semánticos:** los componentes usan `bg-surface`, `text-ink`, `border-line`, etc. — nunca colores hardcoded de Tailwind (`slate-*`, `white`). Así heredan dark mode automáticamente.
- **Docblock Twig:** cada archivo documenta sus parámetros en un comentario `{# ... #}` inicial — es la fuente canónica de la API.

## Tokens semánticos

Definidos en `resources/styles/main.css`. Variables CSS en runtime puenteadas a Tailwind v4 vía `@theme inline`:

| Utilidad | Variable | Light | Dark | Uso |
|---|---|---|---|---|
| `bg-page` | `--rs-page` | `#f8fafc` (slate-50) | `#0f172a` (slate-900) | Fondo de la página |
| `bg-surface` | `--rs-surface` | `#ffffff` | `#1e293b` (slate-800) | Tarjetas, header, modal |
| `text-ink` | `--rs-ink` | `#0f172a` | `#f8fafc` | Texto primario |
| `text-ink-muted` | `--rs-ink-muted` | `#475569` | `#94a3b8` | Texto secundario |
| `text-ink-faint` | `--rs-ink-faint` | `#94a3b8` | `#64748b` | Texto decorativo |
| `border-line` | `--rs-line` | `#e2e8f0` | `#334155` | Bordes principales |

**Dark mode (spec §8.2):** media query `prefers-color-scheme: dark` — sin toggle manual. El media query solo reasigna los valores de las variables; las utilidades no cambian.

**Tokens de marca:** la paleta `brand-*` (50–950) vive en el bloque `@theme` de `main.css`. Para personalizar el starter, redefinir ahí los valores — los componentes los consumen como `bg-brand-600`, `text-brand-700`, etc.

## Catálogo

### `icon.twig`

SVG inline desde el árbol versionado `resources/icons/` (spec §12.1, entrada 8). Los sets se sincronizan desde npm con `bin/sync-icons.sh` y quedan commiteados: producción nunca lee `node_modules` y el diff de cada sync muestra exactamente qué cambió.

| Set | Fuente | Contenido | Licencia |
|---|---|---|---|
| `line-md` (primario) | npm `line-md` | ~1.200 iconos Material Line **animados** — CSS embebida con guard nativo de `prefers-reduced-motion` | MIT |
| `material` (respaldo) | npm `@material-design-icons/svg` (variante `round`) | ~2.100 iconos Material estáticos, misma geometría que line-md | Apache-2.0 |
| `spinner` | npm `svg-spinners` | 46 spinners (CSS embebida) | MIT |

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `name` | string | — | `'name'` (set primario) o `'set:name'` (`'material:mail'`, `'spinner:180-ring'`). Desconocido → no renderiza nada. Si el icono falta en el set pedido, hay **fallback en cadena** al resto del registro |
| `classes` | string | `w-5 h-5` | Clases del `<svg>` |
| `stroke` | int | — | DEPRECADO — el trazo (2, round) viene de los archivos fuente; se ignora |

```twig
{% include 'components/icon.twig' with { name: 'arrow-right', classes: 'w-4 h-4' } only %}
{% include 'components/icon.twig' with { name: 'spinner:12-dots-scale-rotate', classes: 'w-8 h-8' } only %}
{# o directamente la función Twig: #}
{{ rs_icon('material:home', { class: 'w-6 h-6' }) }}
```

> Para añadir un icono: búscalo en [line-md](https://icon-sets.iconify.design/line-md/) o [Material Symbols](https://fonts.google.com/icons), usa `set:nombre` — si existe en `resources/icons/<set>/`, se renderiza. Actualizar sets: `bun update line-md @material-design-icons/svg svg-spinners && bun run icons:sync`. Cada SVG lleva `data-rs-icon` y los iconos heredan `currentColor`.

### `button.twig`

Botón pill con focus rings visibles y feedback `active:scale-95`.

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `label` | string | — | Texto (usar embed + block `content` para contenido rico) |
| `variant` | `primary` \| `secondary` \| `ghost` | `primary` | Estilo |
| `href` | string? | — | Si se define renderiza `<a>`, si no `<button>` |
| `type` | string | `button` | Tipo del `<button>` |
| `full` | bool | `false` | Ancho completo (formularios) |
| `disabled` | bool | `false` | Opacidad + `pointer-events-none` + atributo |
| `classes` | string? | — | Clases extra |

```twig
{% include 'components/button.twig' with { label: 'Enviar', variant: 'primary' } %}

{% embed 'components/button.twig' with { href: '/blog' } %}
    {% block content %}Ver blog <span aria-hidden="true">→</span>{% endblock %}
{% endembed %}
```

### `input.twig`

Input/textarea con label y estado de error.

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `name` | string | — | Name del campo (obligatorio; `id` hereda) |
| `label` | string | — | Texto del label |
| `type` | `text` \| `email` \| `textarea` | `text` | Tipo |
| `value` | string? | — | Valor actual (p.ej. `old.name` tras fallo de validación) |
| `placeholder` | string? | — | Placeholder |
| `error` | string? | — | Mensaje (pinta borde rojo + texto debajo) |
| `id` | string? | `name` | Override del id |
| `rows` | int | `4` | Filas del textarea |

```twig
{% include 'components/input.twig' with {
    name: 'email',
    label: 'Email',
    type: 'email',
    value: old.email ?? '',
    error: errors.email ?? null,
} %}
```

### `card.twig`

Tarjeta para blog/portfolio con CTA.

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `title` | string | — | Título |
| `url` | string | — | Enlace (título e imagen enlazan aquí) |
| `excerpt` | string? | — | Resumen corto |
| `image` | string? | — | URL de imagen (sin imagen, no renderiza el bloque) |
| `meta` | string? | — | Fecha/autor u otro metadato (chip superior) |
| `link_label` | string | `Leer más` | Texto del CTA |

```twig
{% for post in posts %}
    {% include 'components/card.twig' with {
        title: post.title,
        url: post.link,
        excerpt: post.preview.length(20),
        image: post.thumbnail ? post.thumbnail.src|resize(600, 400) : null,
        meta: post.date|date('d M, Y'),
    } only %}
{% endfor %}
```

### `alert.twig`

Alerta semántica con icono (vía `icon.twig`: `material:check_circle`, `material:cancel`, `material:error`, `info`). Dos modos de uso:

```twig
{# Simple (include): message como texto #}
{% include 'components/alert.twig' with {
    variant: 'success',
    title: '¡Listo!',
    message: 'Todo salió bien.',
} %}

{# Rico (embed): block content con HTML libre #}
{% embed 'components/alert.twig' with { variant: 'error', title: 'Ups' } %}
    {% block content %}<p>Detalle del error con <strong>formato</strong>.</p>{% endblock %}
{% endembed %}
```

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `variant` | `success` \| `error` \| `warning` \| `info` | `info` | Semántica (paleta con alpha translúcido, correcta en ambos modos) |
| `title` | string? | — | Título |
| `message` | string? | — | Texto (solo modo include) |

### `badge.twig`

Etiqueta/chip para categorías, tags, estados o contadores.

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `label` | string | — | Texto del badge |
| `href` | string? | — | Si se define renderiza `<a>`, si no `<span>` |
| `variant` | `brand` \| `neutral` \| `success` \| `warning` \| `error` | `neutral` | Paleta |
| `classes` | string? | — | Clases extra |

```twig
{% include 'components/badge.twig' with { label: category.name, href: category.link, variant: 'brand' } only %}
```

### `pagination.twig`

Paginación numerada con flechas prev/next. Usa el Pagination que Timber
adjunta al PostQuery (`posts.pagination`); el controller debe pasar
`'paged' => max(1, (int) get_query_var('paged'))` a `Timber::get_posts()`.

| Prop | Tipo | Default | Descripción |
|---|---|---|---|
| `posts` | PostQuery | — | Query de Timber con `.pagination` (sin esto no renderiza nada) |
| `htmx` | object\|null | `null` | Activa la variante HTMX (sin esto, enlaces clásicos) |

La variante `htmx` espera `{ endpoint, params, target, push_url?, base? }`:
cada enlace pide el fragmento vía `hx-get` y swappea solo `target`; los
`<a>` conservan su `href` real como fallback sin JS. Con `push_url: true`,
`hx-push-url` lleva la URL bonita (`base` + `page/N/`) — la URL compartible
de la jerarquía, no la del fetch a admin-ajax.

```twig
{% include 'components/pagination.twig' with { posts: posts } only %}

{# Variante HTMX (la construye partials/archive-grid.twig): #}
{% include 'components/pagination.twig' with {
    posts: posts,
    htmx: {
        endpoint: site.url ~ '/wp-admin/admin-ajax.php',
        params: { action: 'rs_archive_page', taxonomy: 'category', term: 'rojas-stack' },
        target: '#archive-grid-…',
        push_url: true,
        base: term.link,
    },
} only %}
```

> Nota CPT: para paginar un archive de CPT propio (p.ej. `example_cpt`), extiende el
> componente resolviendo el paginado contra el query de ese post type — la base
> `/page/N` del main query solo cubre `post`.

## Componentes de layout (no del catálogo)

`components/header.twig` y `components/footer.twig` son el layout del sitio (sticky header con menú programático, footer con marca); `components/search-modal.twig` es el modal de búsqueda (Alpine abre/cierra, HTMX consulta `admin-ajax?action=rs_live_search` y hace `hx-swap` de `partials/search-results.twig` en `#search-results`; mientras el fetch vuela, un skeleton de filas gemelas de los resultados reales reemplaza al placeholder en flujo — CSS puro vía la clase `htmx-request` que HTMX añade al indicador, sin Alpine ni overlays). `partials/archive-grid.twig` es el fragmento equivalente de archives: HTMX consulta `admin-ajax?action=rs_archive_page` y swappea grid+paginación dentro del scope Alpine `archiveView` (skeleton de carga, scroll y flash). `partials/archive-shell.twig` envuelve al grid SIN swappearse: lleva el scope `x-data="archiveView"` y mientras el fetch vuela el skeleton de tarjetas `.rs-skeleton` (shimmer con tokens del tema) REEMPLAZA al grid real en el flujo del documento — `x-show` mutuamente excluyentes, sin `position:absolute`: el skeleton nunca tapa paginación ni footer, y como replica el shape del card real (sin placeholder de imagen, como el card) la transición es visualmente estable. Su prop `cards` (default 6 = POSTS_PER_PAGE) define cuántas tarjetas skeleton renderizar. No son componentes de propósito general — se incluyen desde `base.twig`.

### daisyUI 5 (2026-09-09)

Los 4 componentes base (button, card, input, alert) migraron sus **internals a clases daisyUI** (`btn`, `card/card-body`, `input`/`textarea` + `input-error`, `alert` + `alert-soft` + colores por rol): **la API Twig (props, embed, catálogo) no cambió** — ningún caller requirió ajustes.

- **Por qué daisyUI:** componentes 100% CSS (cero JS — HTMX y Alpine siguen siendo los únicos runtimes de comportamiento), plugin nativo de Tailwind v4 (`@plugin "daisyui"`), y generación on-demand: solo las clases presentes en las vistas llegan al bundle.
- **Theming:** sin temas built-in (`themes: false`). Los temas custom `rojas` (default) y `rojas-dark` (`prefersdark: true` → media query, sin toggle) mapean la MISMA paleta que los tokens `--rs-*` — los roles (`primary`, `base-100`, `error`...) toman los valores del §8.1. Si cambias un color, cámbialo en ambos bloques (refactor futuro: derivar `--rs-*` de los vars daisy).
- **Nuevos componentes:** para UI nueva prefiere clases daisyUI del catálogo (~60: dropdown, modal, tabs, toast, tooltip, drawer, rating...) antes que inventar CSS custom; envuélvelas en un componente Twig con props cuando se reutilice.
- **Ronda 1 descartados:** Flowbite (runtime JS propio + pisaba el theming), shadcn/Chakra (React-only), HeadlessUI (React/Vue), Pines (snippets sueltos, sin sistema de theming).

## Extender el catálogo

1. Crear `resources/views/components/mi-componente.twig` con docblock de parámetros.
2. Usar solo tokens semánticos (`bg-surface`, `text-ink`...) + `brand-*` — nada de slate/white hardcoded.
3. Añadir una sección en `resources/views/design-system.twig` para verlo en `/__components`.
4. Actualizar este documento con la tabla de props.
