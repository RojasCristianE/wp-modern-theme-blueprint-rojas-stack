import { expect, test } from '@playwright/test';

/*
 * Smoke test del blog (ruta virtual /blog, módulo 'blog').
 * Verifica que el feed de posts usa el componente card.twig y que la
 * respuesta se sirve como 200 (el router re-marca el 404 de WP, spec §12.1).
 */

test('el blog renderiza posts con el componente card', async ({ page }) => {
  await page.goto('/blog/');

  await expect(
    page.getByRole('heading', { name: 'Últimos Posts' }),
  ).toBeVisible();

  // Card adoptado: article con título enlazado y CTA del componente
  const card = page.locator('article').first();
  await expect(card.getByRole('heading', { level: 3 })).toBeVisible();
  await expect(card.getByRole('link', { name: /Leer más/ })).toBeVisible();
  await expect(card.getByText(/^\d{2} \w+, \d{4}$/)).toBeVisible();
});

test('la ruta virtual responde HTTP 200 con título sin 404', async ({
  page,
}) => {
  const response = await page.goto('/blog/');

  expect(response.status()).toBe(200);
  await expect(page).toHaveTitle(/^(?!.*404).*$/i);
});
