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

Alerta semántica con icono. Dos modos de uso:

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

## Componentes de layout (no del catálogo)

`components/header.twig` y `components/footer.twig` son el layout del sitio (sticky header con menú programático, footer con marca); `components/search-modal.twig` es el modal de búsqueda con Alpine. No son componentes de propósito general — se incluyen desde `base.twig`.

## Extender el catálogo

1. Crear `resources/views/components/mi-componente.twig` con docblock de parámetros.
2. Usar solo tokens semánticos (`bg-surface`, `text-ink`...) + `brand-*` — nada de slate/white hardcoded.
3. Añadir una sección en `resources/views/design-system.twig` para verlo en `/__components`.
4. Actualizar este documento con la tabla de props.
