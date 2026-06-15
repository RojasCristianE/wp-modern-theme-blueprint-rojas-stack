import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';
import path from 'path';

export default defineConfig({
  plugins: [
    liveReload([
      path.resolve(__dirname, './**/*.php'),
      path.resolve(__dirname, './resources/views/**/*.twig'),
    ]),
  ],
  
  // Configuración de la construcción
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'resources/scripts/main.js'),
        styles: path.resolve(__dirname, 'resources/styles/main.css'),
      },
    },
  },

  // Servidor de desarrollo optimizado para DDEV/Docker
  server: {
    cors: true,
    strictPort: true,
    port: 5173,
    hmr: {
      host: 'localhost',
    },
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources'),
    },
  },
});
