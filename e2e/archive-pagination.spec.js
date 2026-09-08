import { expect, test } from '@playwright/test';

/*
 * Paginación HTMX de archives (categoría/etiqueta): hx-get al endpoint
 * rs_archive_page → hx-swap de [data-archive-grid] sin recargar la
 * página, con hx-push-url para URL compartible y href real como
 * fallback. Datos: bin/seed-demo-content.sh (15 posts demo;
 * rojas-stack tiene 10 y posts_per_page=6 → 2 páginas (6+4), tag htmx 7
 * → 2 páginas (6+1)).
 */

test('la paginación de categoría swappea el grid sin recargar', async ({
  page,
}) => {
  await page.goto('/category/rojas-stack/');
  const grid = page.locator('[data-archive-grid]').first();

  await expect(grid.locator('article')).toHaveCount(6);
  await expect(page).toHaveURL(/\/category\/rojas-stack\/$/);

  // Marcador de sesión: si hubiera recarga completa, desaparecería.
  await page.evaluate(() => {
    window.__noReloadMarker = true;
  });

  await grid.locator('a[aria-label="Página siguiente"]').click();

  // hx-push-url deja la URL de la página 2 compartible.
  await expect(page).toHaveURL(/\/category\/rojas-stack\/page\/2\/$/);
  await expect(grid.locator('article')).toHaveCount(4);
  await expect(grid.locator('[aria-current="page"]')).toHaveText('2');
  expect(await page.evaluate(() => window.__noReloadMarker)).toBe(true);

  // El scope Alpine del archive sobrevive al swap (no es un full load).
  await expect(page.locator('[x-data="archiveView"]')).toHaveCount(1);

  // Y se puede volver a la página 1 con el enlace numerado.
  await grid.getByRole('link', { name: '1', exact: true }).click();
  await expect(page).toHaveURL(/\/category\/rojas-stack\/$/);
  await expect(grid.locator('[aria-current="page"]')).toHaveText('1');
});

test('la paginación de etiqueta swappea el grid sin recargar', async ({
  page,
}) => {
  await page.goto('/tag/htmx/');
  const grid = page.locator('[data-archive-grid]').first();

  await expect(grid.locator('article')).toHaveCount(6);

  await grid.locator('a[aria-label="Página siguiente"]').click();

  await expect(page).toHaveURL(/\/tag\/htmx\/page\/2\/$/);
  await expect(grid.locator('article')).toHaveCount(1);
  await expect(grid.locator('[aria-current="page"]')).toHaveText('2');
});

test('la carga directa de una página profunda renderiza server-side', async ({
  page,
}) => {
  // Fallback sin JS: la jerarquía resuelve la página completa con paginación.
  await page.goto('/category/rojas-stack/page/2/');
  await expect(page.locator('[data-archive-grid] article')).toHaveCount(4);
  await expect(page.locator('[aria-current="page"]')).toHaveText('2');
});

test('el skeleton cubre el grid mientras HTMX trae el fragmento', async ({
  page,
}) => {
  // Ralentizamos artificialmente el fetch del fragmento (400 ms) para
  // poder observar el overlay .rs-skeleton entre request y swap.
  await page.route(/rs_archive_page/, async (route) => {
    await new Promise((resolve) => setTimeout(resolve, 400));
    await route.continue();
  });

  await page.goto('/category/rojas-stack/');
  const grid = page.locator('[data-archive-grid]').first();
  const skeleton = page.locator(
    '[data-archive-shell] [x-show="requestCount > 0"]',
  );

  // Estado inicial: sin skeleton.
  await expect(skeleton).toBeHidden();
  await page.evaluate(() => {
    window.__noReloadMarker = true;
  });

  await grid.locator('a[aria-label="Página siguiente"]').click();

  // Durante el fetch: skeleton visible, grid real oculto (sin saltos de layout).
  await expect(skeleton).toBeVisible();

  // Tras el swap: skeleton fuera, grid fresco, sin recarga.
  await expect(skeleton).toBeHidden({ timeout: 5_000 });
  await expect(grid.locator('article')).toHaveCount(4);
  await expect(page.locator('[x-data="archiveView"]')).toHaveCount(1);
  expect(await page.evaluate(() => window.__noReloadMarker)).toBe(true);
});
