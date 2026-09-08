import '../styles/main.css';
import Alpine from 'alpinejs';
import htmx from 'htmx.org';

// Exponer HTMX globalmente para que Alpine pueda usarlo en atributos
window.htmx = htmx;

// Componentes Alpine (micro-interacciones efímeras; el estado de datos
// vive en el servidor — regla §3 del stack).
//
// archiveView: acompaña a partials/archive-grid.twig en las páginas de
// archive (categoría/etiqueta/blog). El shell con x-data queda fuera del
// nodo que HTMX reemplaza, así que el componente sobrevive a cada swap:
//   - requestCount: peticiones HTMX del grid en vuelo → archive-shell
//     muestra el skeleton EN FLUJO (reemplaza al grid real; sin overlay
//     absoluto) y oculta el grid hasta el swap.
//   - scroll:   vuelve al inicio del grid al cambiar de página.
//   - flash:    fade-in del grid fresco (Web Animations API).
Alpine.data('archiveView', () => ({
  requestCount: 0,

  init() {
    document.body.addEventListener('htmx:beforeRequest', (e) => {
      if (e.detail?.elt?.closest?.('[data-archive-grid]')) {
        this.requestCount += 1;
      }
    });

    document.body.addEventListener('htmx:afterSwap', (e) => {
      const grid = e.detail?.target?.closest?.('[data-archive-grid]');
      if (!grid) {
        return;
      }

      this.requestCount = 0;

      // Volver al inicio del grid si la paginación quedó fuera de vista.
      const top = grid.getBoundingClientRect().top + window.scrollY - 96;
      if (Math.abs(window.scrollY - top) > 40) {
        window.scrollTo({ top, behavior: 'smooth' });
      }

      // Flash de entrada del contenido fresco.
      grid.animate?.([{ opacity: 0.25 }, { opacity: 1 }], {
        duration: 260,
        easing: 'ease-out',
      });
    });
  },
}));

// Iniciar AlpineJS
window.Alpine = Alpine;
Alpine.start();

// Configurar HTMX para que escanee nuevos elementos inyectados por Alpine
document.addEventListener('alpine:initialized', () => {
  htmx.process(document.body);
});

console.log('🚀 Rojas Stack: Frontend initialized (HTMX + AlpineJS ready)');
