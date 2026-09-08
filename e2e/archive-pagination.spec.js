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
