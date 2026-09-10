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

test('el menú móvil swappea bars-3 ↔ x-mark y abre el panel en flujo', async ({
  page,
}) => {
  await page.setViewportSize({ width: 375, height: 667 });
  await page.goto('/');

  const trigger = page.getByRole('button', { name: 'Menú' });
  const panel = page.locator('#mobile-nav');

  // Cerrado: sin swap-active, panel oculto (Alpine x-cloak/x-show)
  await expect(trigger).toBeVisible();
  await expect(trigger).not.toHaveClass(/swap-active/);
  await expect(trigger).toHaveAttribute('aria-expanded', 'false');
  await expect(panel).toBeHidden();

  await trigger.click();

  // Abierto: swap daisyUI activo (bars-3 → x-mark) + panel visible
  await expect(trigger).toHaveClass(/swap-active/);
  await expect(trigger).toHaveAttribute('aria-expanded', 'true');
  await expect(panel).toBeVisible();
  // El swap contiene ambos iconos (el estado solo alterna opacidad)
  await expect(trigger.locator('[data-rs-icon="x-mark"]')).toBeAttached();
  await expect(trigger.locator('[data-rs-icon="bars-3"]')).toBeAttached();
  await expect(panel.getByRole('link', { name: 'Inicio' })).toBeVisible();
  await expect(panel.getByRole('link', { name: 'Blog' })).toBeVisible();
  await expect(panel.getByRole('link', { name: 'Contacto' })).toBeVisible();

  // El trigger de búsqueda del panel abre el modal único (evento window)
  await panel.getByRole('button', { name: 'Buscar' }).click();
  await expect(panel).toBeHidden();
  await expect(page.locator('input[name="s"]')).toBeVisible();

  // Escape cierra el modal; el panel ya estaba cerrado
  await page.keyboard.press('Escape');
  await expect(page.locator('input[name="s"]')).toBeHidden();

  // Navegar desde el panel cierra y lleva a /blog
  await trigger.click();
  await expect(panel).toBeVisible();
  await panel.getByRole('link', { name: 'Blog' }).click();
  await expect(page).toHaveURL(/\/blog/);
});
