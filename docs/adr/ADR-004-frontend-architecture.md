# ADR-004: Laravel Blade + Tailwind CSS v4 + Vanilla JS Frontend Architecture

**Date:** 2026-11-17  
**Status:** `Accepted (Implemented)`  
**Deciders:** Principal Software Architect, Project Owner  
**Related Docs:** `docs/architecture/frontend-architecture.md`, ## 1. Context

The project requires a responsive, fast, maintainable CRM interface (data tables, 360-degree entity views, Kanban pipeline board, and dynamic quotation builder) without introducing heavy single-page application (SPA) frameworks.

## 2. Proposed Decision

1. **Templating:** Use **Laravel Blade** anonymous and class-based components (`resources/views/components/`).
2. **Styling:** Use **[Tailwind CSS v4](https://tailwindcss.com/docs/upgrade-guide)** with CSS-first `@import "tailwindcss";` and `@theme` tokens compiled via `@tailwindcss/vite` (no legacy `tailwind.config.js`).
3. **Interactivity:** Use modular **Vanilla ES2022 JavaScript** (`resources/js/modules/`), introducing **Alpine.js** only when localized declarative state (e.g., complex multi-item quotation form or interactive Kanban drawer) demonstrably reduces code complexity. React, Vue, Angular, Svelte, Inertia, and Livewire are strictly excluded.

## 3. Consequences

- **Positive:** Minimal bundle size, zero client/server state synchronization bugs, sub-second Vite builds, direct compatibility with Laravel validation and Blade authorization directives (`@can`).
- **Negative / Mitigation:** Interactive widgets (Kanban drag-and-drop, quote calculator) require clean modular Vanilla JS patterns—documented in `docs/architecture/frontend-architecture.md`.
