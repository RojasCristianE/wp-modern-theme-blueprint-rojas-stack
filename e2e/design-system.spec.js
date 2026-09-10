import { existsSync, readFileSync } from 'node:fs';
import { expect, test } from '@playwright/test';

/*
 * Smoke test del catálogo de componentes (/__components).
 * Solo corre cuando el módulo design_system está activo (WP_ENV=development).
 *
 * .env es gitignored: en un clone fresco aún no existe (ddev start lo
 * crea/configura). Se lee de forma defensiva — sin .env, el test se
 * salta en vez de romper toda la suite.
 */

const envPath = new URL('../.env', import.meta.url);
const envFile = existsSync(envPath) ? readFileSync(envPath, 'utf8') : '';
const wpEnv = envFile.match(/^WP_ENV=(.*)$/m)?.[1]?.trim();

test.skip(wpEnv !== 'development', 'design_system requiere WP_ENV=development');

test('el catálogo de componentes renderiza todas las secciones', async ({
  page,
}) => {
  await page.goto('/__components');

  await expect(
    page.getByRole('heading', { level: 1, name: 'Design System' }),
  ).toBeVisible();

  for (const section of [
    'Botones',
    'Inputs',
    'Alertas',
    'Cards',
    'Badges',
    'Iconos',
  ]) {
    await expect(page.getByRole('heading', { name: section })).toBeVisible();
  }

  // Variantes del botón primary y un estado deshabilitado
  await expect(page.getByRole('button', { name: 'Primario' })).toBeVisible();
  await expect(
    page.getByRole('button', { name: 'Deshabilitado' }),
  ).toBeDisabled();

  // Un input con estado de error
  await expect(page.locator('#demo_error')).toHaveValue('no-valido');

  // Badges y catálogo de iconos
  await expect(page.getByText('Brand', { exact: true })).toBeVisible();
  await expect(page.locator('svg[aria-hidden="true"]').first()).toBeAttached();

  // Catálogo de iconos sincronizados (spec §12.1, entrada 8): el atributo
  // refleja el nombre pedido; 'heroicons-solid:*' es set explícito.
  await expect(
    page.locator('[data-rs-icon="magnifying-glass"]').first(),
  ).toBeAttached();
  await expect(
    page.locator('[data-rs-icon="envelope"]').first(),
  ).toBeAttached();
  await expect(
    page.locator('[data-rs-icon="heroicons-solid:check-circle"]').first(),
  ).toBeAttached();

  // Animación de iconos: daisyUI swap + loading (CSS puro). El header
  // (swap del hamburger) precede al catálogo en el DOM y el spinner del
  // load screen (#rs-load) precede al del catálogo: se scopea a main.
  await expect(page.locator('main .swap.swap-rotate').first()).toBeVisible();
  await expect(page.locator('main .loading-spinner').first()).toBeVisible();
});
