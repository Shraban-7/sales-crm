# Quality Assurance & Static Analysis Gates — Sales CRM

**Last Updated:** 2026-11-17  
**Implementation Status:** `Proposed (Active QA Standard)`  
**Related Docs:** `CONTRIBUTING.md`, `docs/architecture/coding-standards.md`, `docs/prompts/pre-release-audit-prompt.md`

## 1. Automated Quality Gate Pipeline

Before any feature branch is merged or marked `Implemented` in `PROJECT_STATUS.md`, it must pass the four QA gates below:

```bash
# 1. Code Style & Formatting Check (Laravel Pint)
./vendor/bin/pint --test

# 2. Static Type Analysis (Larastan / PHPStan)
./vendor/bin/phpstan analyse --memory-limit=1G

# 3. Full Automated Test Suite (Pest / PHPUnit)
php artisan test --parallel

# 4. Frontend Asset Compilation Check (Vite + Tailwind CSS v4)
npm run build
```

## 2. Manual UI & UX Acceptance Criteria

- **Keyboard & Screen Reader Navigation:** Modals trap focus appropriately and close on `Escape`; form inputs have linked `<label for="...">` elements and `aria-describedby` validation error messages.
- **Empty States:** Every table (`Leads`, `Contacts`, `Companies`, `Deals`, `Quotations`, `Tasks`) displays a clean, helpful empty-state card with a primary call-to-action button when 0 records match.
- **Loading & Double-Submit Protection:** Form submit buttons disable themselves on submit (`data-submitting`) to prevent accidental duplicate POST requests.
