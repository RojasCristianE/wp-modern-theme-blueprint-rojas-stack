import { expect, test } from '@playwright/test';

/*
 * Smoke test de la homepage.
 *
 * Verifica que el stack completo renderiza de punta a punta:
 * base.twig (header/main/footer), el menú programático de
 * MenuServiceProvider y el hero de index.twig con Carbon Fields.
 */

test('la homepage renderiza header, menú, contenido y footer', async ({
  page,
}) => {
  const pageErrors = [];
  page.on('pageerror', (error) => pageErrors.push(error));

  await page.goto('/');

  // Header sticky visible (base.twig)
  await expect(page.getByRole('banner')).toBeVisible();

  // Menú programático inyectado por MenuServiceProvider
  await expect(page.getByRole('link', { name: 'Inicio' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Blog' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Contacto' })).toBeVisible();

  // Hero de index.twig dentro de <main id="content">
  const main = page.locator('#content');
  await expect(main).toBeVisible();
  await expect(main.getByRole('heading', { level: 1 })).toBeVisible();

  // Footer con la marca (footer.twig)
  await expect(page.getByRole('contentinfo')).toContainText('Rojas Stack');

  // Sin excepciones JS sin capturar (Alpine, HTMX, Vite client)
  expect(pageErrors).toEqual([]);
});
