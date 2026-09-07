import { expect, test } from '@playwright/test';

/*
 * Smoke test del blog (ruta virtual /blog, módulo 'blog').
 * Verifica que el feed de posts usa el componente card.twig.
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
