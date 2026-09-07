import { defineConfig, devices } from '@playwright/test';

/*
 * Configuración de Playwright (smoke tests E2E).
 *
 * Requisito: el entorno DDEV debe estar levantado (`ddev start`),
 * ya que los tests apuntan al sitio real, no a un servidor efímero.
 * La URL base es sobre-escribible para apuntar a staging/prod:
 *   PLAYWRIGHT_BASE_URL=https://staging.example.com bun run test:e2e
 */

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? 'github' : 'list',
  use: {
    baseURL:
      process.env.PLAYWRIGHT_BASE_URL ??
      'https://wp-modern-theme-blueprint.ddev.site',
    // DDEV usa certificados mkcert locales
    ignoreHTTPSErrors: true,
    // Usa el Chrome del sistema; para el binario de Playwright:
    // `bunx playwright install chromium` y quitar `channel`
    channel: 'chrome',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
