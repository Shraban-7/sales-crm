# Regression Testing Protocol — Sales CRM

**Last Updated:** 2026-11-17  
**Implementation Status:** `Proposed (Active QA Standard)`  
**Related Docs:** `docs/testing/testing-strategy.md`, `docs/prompts/testing-prompt.md`

## 1. Bug-Fix Regression Rule

Whenever a defect is discovered in **Sales CRM**:
1. Reproduce the defect first by writing a failing automated test in `tests/Feature/` or `tests/Unit/` before modifying application code.
2. Implement the fix in the appropriate Action, Service, Policy, or Blade view.
3. Verify the new regression test passes alongside the full test suite (`php artisan test --parallel`).

## 2. High-Risk Regression Suites to Run Before Every Release

1. **Tenant / Team Isolation Suite (`tests/Feature/Security/IdorIsolationTest.php`):** Verifies that no new route or query scope accidentally leaks records across `team_id` or `owner_id`.
2. **Financial Precision Suite (`tests/Unit/Quotations/QuotationCalculationTest.php`):** Verifies tax, discount, and deal value synchronization across boundary numbers (`0.00`, `0.01`, `999999999.99`, `33.33%` discounts).
3. **N+1 Query Detection Suite (`tests/Feature/Performance/ZeroLazyLoadingTest.php`):** Hits every index, Kanban, show, and report page with 10 seeded records and asserts `Model::preventLazyLoading()` triggers zero exceptions.
