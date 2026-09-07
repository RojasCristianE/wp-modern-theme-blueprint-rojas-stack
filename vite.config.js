import { defineConfig } from 'vite';
import { v4wp } from '@kucrut/vite-for-wp';
import { dev_server } from '@kucrut/vite-for-wp/plugins';
import liveReload from 'vite-plugin-live-reload';
import path from 'node:path';

/*
 * Build con vite-for-wp (spec §7.1):
 * - v4wp: configura manifest.json en dist/ para el lado PHP (kucrut/vite-for-wp).
 * - dev_server: mientras `bun run dev` corre, escribe dist/vite-dev-server.json
 *   (el helper PHP lo detecta y sirve los assets desde el dev server con HMR).
 *
 * El CSS viaja en el bundle: main.js importa resources/styles/main.css.
 */
export default defineConfig({
  plugins: [
    v4wp({
      input: 'resources/scripts/main.js',
      outDir: 'dist',
    }),
    dev_server(),
    liveReload([
      path.resolve(__dirname, './**/*.php'),
      path.resolve(__dirname, './resources/views/**/*.twig'),
    ]),
  ],

  // Servidor de desarrollo optimizado para DDEV/Docker.
  // host/port son normalizados por dev_server (origin + HMR + strictPort).
  server: {
    cors: true,
    host: 'localhost',
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources'),
    },
  },
});
