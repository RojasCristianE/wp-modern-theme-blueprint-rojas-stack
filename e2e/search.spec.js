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

test('el skeleton de filas reemplaza los resultados mientras el fetch vuela', async ({
  page,
}) => {
  // Ralentizamos el fetch del fragmento (1s) para dar a las aserciones de
  // Playwright una ventana de visibilidad holgada (el input tiene su
  // propio delay de 300ms antes de disparar la petición).
  await page.route(/rs_live_search/, async (route) => {
    await new Promise((resolve) => setTimeout(resolve, 1000));
    await route.continue();
  });

  await page.goto('/');
  await page.getByRole('button', { name: 'Buscar' }).click();

  const results = page.locator('#search-results');
  const skeleton = page.locator('#search-skeleton');
  const placeholder = results.locator('.rs-search-placeholder');

  // Estado inicial: placeholder visible, skeleton oculto (CSS puro, sin Alpine).
  await expect(placeholder).toBeVisible();
  await expect(skeleton).toBeHidden();

  await page.getByPlaceholder('Escribe para buscar...').fill('hello');

  // Durante el fetch: skeleton EN FLUJO (reemplazo, no overlay) y sin
  // posicionamiento absoluto que tape footer u otros elementos.
  await expect(skeleton).toBeVisible();
  await expect(placeholder).toBeHidden();
  await expect(skeleton.locator('.rs-skeleton').first()).toBeVisible();
  await expect(results.locator('.absolute')).toHaveCount(0);

  // Tras el swap: skeleton fuera, resultados reales dentro.
  await expect(skeleton).toBeHidden({ timeout: 5_000 });
  await expect(results.locator('a').first()).toHaveAttribute(
    'href',
    /hello-world/,
  );
});
