import '../styles/main.css';
import Alpine from 'alpinejs';
import htmx from 'htmx.org';

// Iniciar AlpineJS
window.Alpine = Alpine;
Alpine.start();

// Exponer HTMX globalmente (opcional pero útil)
window.htmx = htmx;

console.log('🚀 Rojas Stack: Frontend initialized (HTMX + AlpineJS ready)');
