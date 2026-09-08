import { expect, test } from '@playwright/test';

/*
 * Paginación HTMX de la ruta virtual /blog/: mismo contrato que los
 * archives de taxonomía (rs_archive_page → [data-archive-grid]) pero
 * con la paginación resuelta por el router (rutas /blog/page/{paged}).
 * La página 1 del blog usa posts_per_page=12 (features.php).
 */

test('la paginación de /blog/ swappea el grid sin recargar', async ({
  page,
}) => {
  await page.goto('/blog/');
  const grid = page.locator('[data-archive-grid]').first();

  await expect(grid.locator('article')).toHaveCount(6);
  await expect(page).toHaveURL(/\/blog\/$/);

  // Marcador de sesión: si hubiera recarga completa, desaparecería.
  await page.evaluate(() => {
    window.__noReloadMarker = true;
  });

  await grid.locator('a[aria-label="Página siguiente"]').click();

  // hx-push-url deja la URL bonita de la página 2 compartible.
  await expect(page).toHaveURL(/\/blog\/page\/2\/$/);
  await expect(grid.locator('article')).toHaveCount(6, { timeout: 10_000 });
  await expect(grid.locator('[aria-current="page"]')).toHaveText('2');
  expect(await page.evaluate(() => window.__noReloadMarker)).toBe(true);

  // El scope Alpine del blog sobrevive al swap (no es un full load).
  await expect(page.locator('[x-data="archiveView"]')).toHaveCount(1);

  // Volver a la página 1.
  await grid.getByRole('link', { name: '1', exact: true }).click();
  await expect(page).toHaveURL(/\/blog\/$/);
  await expect(grid.locator('[aria-current="page"]')).toHaveText('1');
});

test('la carga directa de /blog/page/2/ renderiza server-side', async ({
  page,
}) => {
  // Sin JS: el router resuelve la ruta virtual con el placeholder {paged}.
  await page.goto('/blog/page/2/');
  await expect(page.locator('[data-archive-grid] article')).toHaveCount(6);
  await expect(page.locator('[aria-current="page"]')).toHaveText('2');
  await expect(page).toHaveTitle(/Page 2/);
});
