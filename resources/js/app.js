import { initKanbanBoard } from './modules/kanban.js';
import { initQuotationCalculator } from './modules/quotation-calculator.js';

function initMobileNav() {
    const drawer = document.querySelector('[data-mobile-nav-drawer]');
    const openBtn = document.querySelector('[data-mobile-nav-open]');
    if (!drawer || !openBtn) return;

    const closeDrawer = () => {
        drawer.classList.add('hidden');
        openBtn.setAttribute('aria-expanded', 'false');
    };

    openBtn.addEventListener('click', () => {
        drawer.classList.remove('hidden');
        openBtn.setAttribute('aria-expanded', 'true');
    });

    drawer.querySelectorAll('[data-mobile-nav-close]').forEach((el) => {
        el.addEventListener('click', closeDrawer);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !drawer.classList.contains('hidden')) {
            closeDrawer();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initKanbanBoard();
    initQuotationCalculator();
});
