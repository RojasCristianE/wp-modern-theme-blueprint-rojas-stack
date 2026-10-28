---
name: seo-unlighthouse
description: Multi-page Lighthouse audit via the MIT-licensed Unlighthouse CLI (`npx unlighthouse --site <url>`). Free-tier alternative to running PageSpeed against every URL on a site, no API quota burn, runs locally. Use for site-wide performance/SEO/accessibility audits of this WordPress theme in DDEV or of any public URL.
metadata:
  version: "2.3.1-rojas.1"
  source: "https://github.com/AgriciDaniel/claude-seo (extensions/unlighthouse/skills/seo-unlighthouse, MIT)"
  vendored: "Adapted to run standalone: no claude-seo plugin wrapper, direct npx usage, unlighthouse ^0.18.0"
compatibility: "Requires Node 18+ (Bun available in this project) and Chrome/Chromium on the host."
---

# seo-unlighthouse (Rojas Stack edition)

Run Lighthouse against every URL on a site (up to a configurable cap)
and aggregate the results. Useful when:

- PageSpeed Insights' free quota (25k QPD) isn't enough for a large site.
- You want offline / local CWV measurement (CI integration, restricted environments).
- You need a quick site-wide regression check after a deploy.

## Prerequisites

- Node 18+ (or Bun) on `$PATH`; Chrome/Chromium installed (Playwright's
  Chromium is reused automatically if present in this project).
- No API key needed. Install on demand — do NOT add `unlighthouse` to
  `package.json` dependencies; always run it via `npx`/`bunx`:

```bash
npx unlighthouse --site https://wp-modern-theme-blueprint.ddev.site   # local DDEV
npx unlighthouse --site https://example.com                            # any public site
```

> **Rojas Stack note:** audit the **DDEV URL** (`https://<project>.ddev.site`),
> never `localhost` ports directly, so WP rewrites resolve correctly.
> Unlighthouse crawls rendered pages — start the environment first
> (`ddev start`) and expect the first run to take minutes on a full site.

## Routing

| Command | Effect |
|---|---|
| `npx unlighthouse --site <url>` | Mobile audit, up to 200 routes, JSON+HTML report in `.unlighthouse/` |
| `npx unlighthouse --site <url> --desktop` | Desktop form factor |
| `npx unlighthouse --site <url> --output-dir ./reports/unlighthouse` | Persist report outside the default dir |

Route cap and per-page timeout are set via a generated `unlighthouse.config.mjs`
passed with `--config-file` (the only CLI-documented way to set `scanner.maxRoutes`;
unlighthouse-ci has no `--max-routes` flag):

```js
// unlighthouse.config.mjs (temp file, do not commit)
export default {
  site: 'https://wp-modern-theme-blueprint.ddev.site',
  scanner: { maxRoutes: 50 },
  lighthouseOptions: { formFactor: 'desktop' },
}
```

## Output handling

Read `ci-result.json` from the output dir (`.unlighthouse/output/ci-result.json`).
The default `jsonSimple` reporter writes a flat JSON array of per-route
results; a tolerant fallback also accepts the `jsonExpanded` object shape.
Summarize it as:

- `route_count`: number of routes actually audited
- `aggregate_scores`: median score per Lighthouse category across all audited
  routes (`performance`, `accessibility`, `best-practices`, `seo`)
- `routes`: the per-route breakdown (also on disk at `<output_dir>/ci-result.json`)

Report the worst routes first, then map each issue to theme source
(`resources/views/**`, `resources/styles/**`, `app/Providers/**`) before fixing.
Respect the project's motion/transition/paint policies (AGENTS.md §Stack) —
do not "fix" performance warnings with `transition-all`, blanket `will-change`,
or JS-driven transitions that duplicate daisyUI choreography.

## When to use Unlighthouse vs. alternatives

| Use Unlighthouse | Use squirrelscan (see `squirrelscan` skill) |
|---|---|
| Per-page Core Web Vitals lab data (Lighthouse) | Site-wide rules audit: SEO, security, leaked secrets, agent experience |
| CWV regression check after a deploy | Fix-loop with code mapping and re-audit scoring |
| Visual HTML report to share | LLM-optimized report + health score |

Run `audit-website` (squirrelscan) for breadth; run Unlighthouse for
per-page performance depth. Both can be combined: squirrelscan first for
the issue list, Unlighthouse to verify CWV after performance fixes.

## Cross-skill delegation

- For single-URL field data (CrUX real-user data), use PageSpeed Insights
  API instead — Unlighthouse is lab data only.
- For rules-based auditing and the fix loop, use the `audit-website` skill.
