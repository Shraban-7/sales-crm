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

function initLoginForm() {
    const form = document.querySelector('[data-login-form]');
    if (!form) return;

    const passwordInput = form.querySelector('#password');
    const toggleBtn = form.querySelector('[data-toggle-password]');
    if (!toggleBtn || !passwordInput) return;

    const eyeOpen = toggleBtn.querySelector('[data-eye-open]');
    const eyeClosed = toggleBtn.querySelector('[data-eye-closed]');

    toggleBtn.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        toggleBtn.setAttribute('aria-pressed', String(isHidden));
        toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        eyeOpen?.classList.toggle('hidden', isHidden);
        eyeClosed?.classList.toggle('hidden', !isHidden);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initLoginForm();
    initKanbanBoard();
    initQuotationCalculator();
});
