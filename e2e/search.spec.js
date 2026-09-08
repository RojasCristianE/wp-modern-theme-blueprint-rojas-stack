import { expect, test } from '@playwright/test';

/*
 * Búsqueda en vivo (spec §9.3): el modal dispara rs_live_search vía HTMX
 * y hace hx-swap del fragmento en #search-results. "hello" matchea el
 * post semilla "Hello world!" del ghost core (LIKE case-insensitive).
 */

test('la búsqueda en vivo renderiza resultados dentro del modal', async ({
  page,
}) => {
  await page.goto('/');

  // Abrir el modal desde el trigger del header
  await page.getByRole('button', { name: 'Buscar' }).click();
  await expect(page.locator('#search-results')).toBeVisible();

  // Escribir un término con resultados: dispara input → delay 300ms → ajax
  await page
    .getByPlaceholder('Escribe para buscar...')
    .fill('hello', { timeout: 5000 });

  // El fragmento (solo resultados, no la página completa) aterriza en el target
  const result = page.locator('#search-results a').first();
  await expect(result).toBeVisible();
  await expect(result).toHaveAttribute('href', /hello-world/);
});

test('un término sin resultados muestra el estado vacío', async ({ page }) => {
  await page.goto('/');

  await page.getByRole('button', { name: 'Buscar' }).click();
  await page
    .getByPlaceholder('Escribe para buscar...')
    .fill('zzzinexistente12345');

  await expect(page.getByText(/No encontramos resultados/)).toBeVisible({
    timeout: 5000,
  });
});
