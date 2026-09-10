import { expect, test } from '@playwright/test';

/*
 * Pipeline de media (spec §12.1): image.twig / rs_image con imgproxy.
 * - Cards: width/height (CLS), loading=lazy, sizes=auto, src del proxy.
 * - Hero (single): fetchpriority=high, SIN lazy.
 * - La URL de variante responde 200 (cadena nginx → imgproxy).
 */

test('los cards del blog usan el pipeline de media', async ({ page }) => {
  await page.goto('/blog/');

  const img = page.locator('main article img').first();

  await expect(img).toBeVisible();
  await expect(img).toHaveAttribute(
    'src',
    /\/cdn-cgi\/image\/w:\d+\/wp-content\/uploads\//,
  );
  await expect(img).toHaveAttribute('width', /\d+/);
  await expect(img).toHaveAttribute('height', /\d+/);
  await expect(img).toHaveAttribute('loading', 'lazy');
  await expect(img).toHaveAttribute('sizes', /.+/);
  await expect(img).toHaveAttribute('srcset', /w:1920/);
});

test('el hero del single es LCP: fetchpriority alta y sin lazy', async ({
  page,
}) => {
  await page.goto('/hello-world/');

  const hero = page.locator('figure img').first();

  await expect(hero).toBeVisible();
  await expect(hero).toHaveAttribute('fetchpriority', 'high');
  await expect(hero).not.toHaveAttribute('loading', 'lazy');
  await expect(hero).toHaveAttribute('src', /w:1280\//);
});

test('una variante del proxy carga como imagen', async ({ page }) => {
  await page.goto('/blog/');

  const src = await page
    .locator('main article img')
    .first()
    .getAttribute('src');

  expect(src).toBeTruthy();
  // src es relativa (/cdn-cgi/...): page.request la resuelve contra baseURL.
  const response = await page.request.get(src ?? '');

  expect(response.status()).toBe(200);
  expect(response.headers()['content-type']).toMatch(/^image\//);
});
