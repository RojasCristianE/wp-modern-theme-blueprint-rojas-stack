import '../styles/main.css';
import Alpine from 'alpinejs';
import htmx from 'htmx.org';

// Exponer HTMX globalmente para que Alpine pueda usarlo en atributos
window.htmx = htmx;

// Iniciar AlpineJS
window.Alpine = Alpine;
Alpine.start();

// Configurar HTMX para que escanee nuevos elementos inyectados por Alpine
document.addEventListener('alpine:initialized', () => {
    htmx.process(document.body);
});

console.log('🚀 Rojas Stack: Frontend initialized (HTMX + AlpineJS ready)');
