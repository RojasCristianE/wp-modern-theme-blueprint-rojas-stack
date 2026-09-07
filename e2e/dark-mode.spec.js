import { expect, test } from '@playwright/test';

/*
 * Dark mode (spec §8.2): media query prefers-color-scheme, sin toggle.
 * Los tokens semánticos (bg-page, bg-surface, text-ink) deben cambiar
 * de valor al emular el esquema de color.
 */

test('los tokens semánticos cambian entre light y dark', async ({ page }) => {
  await page.goto('/');

  // Light: --rs-page = slate-50
  await page.emulateMedia({ colorScheme: 'light' });
  const lightBody = await page.evaluate(
    () => getComputedStyle(document.body).backgroundColor,
  );
  expect(lightBody).toBe('rgb(248, 250, 252)');

  // Dark: --rs-page = slate-900 (spec §8.2)
  await page.emulateMedia({ colorScheme: 'dark' });
  const darkBody = await page.evaluate(
    () => getComputedStyle(document.body).backgroundColor,
  );
  expect(darkBody).toBe('rgb(15, 23, 42)');

  // Dark: header usa --rs-surface = slate-800 (se lee el token crudo:
  // el computed style de color-mix en oklab varía según el browser)
  const surfaceVar = await page
    .getByRole('banner')
    .evaluate((el) =>
      getComputedStyle(el).getPropertyValue('--rs-surface').trim(),
    );
  expect(surfaceVar).toBe('#1e293b');
});
