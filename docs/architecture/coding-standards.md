# Coding Standards & Style Guide — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Active Governance Standard (Verified)`  
**Related Docs:** `CONTRIBUTING.md`, `docs/architecture/backend-architecture.md`

## 1. PHP & Laravel Coding Standards

1. **Strict Types & PSR-12:**
   - Every `.php` file (except Blade templates) must begin with `declare(strict_types=1);`.
   - Format code using **Laravel Pint** (`./vendor/bin/pint --test` in CI) following the `"laravel"` preset.
2. **Type Declarations:**
   - Every method parameter, return type, and class property must have an explicit PHP 8.3+ type hint (including union/intersection types and `void` / `never`).
   - Use PHPDoc `@param` / `@return` generics (`@return Collection<int, Deal>`) where array/collection item types are needed for static analysis (PHPStan / Larastan Level 6+).
3. **Final & Readonly Classes:**
   - Mark Action classes, DTOs, Controllers, and Jobs as `final` (and `readonly` where stateless) to favour composition over inheritance.
4. **Configuration & Constants:**
   - Never call `env()` outside `config/*.php`.
   - Use PHP 8.1+ backed `enum` classes in `app/Enums/` instead of magic strings or class constants.

## 2. Eloquent & Database Standards

- **No Raw Unbound SQL:** Never interpolate variables into `DB::raw()`, `whereRaw()`, or `orderByRaw()`. Always pass parameter bindings (`whereRaw('value * probability >= ?', [$threshold])`).
- **Explicit Mass Assignment:** Always define `protected $fillable = [...]` on models. Never set `protected $guarded = []`.
- **Query Scopes:** Encapsulate reusable query filters in typed local scopes (`public function scopeVisibleTo(Builder $query, User $user): Builder`).

## 3. Blade & Tailwind CSS v4 Standards

- **Componentization:** Extract any UI pattern used 2+ times into `resources/views/components/`.
- **No Database Queries in Views:** Never call `Model::where(...)` or un-eager-loaded relationships inside `.blade.php` files.
- **Tailwind CSS v4 Class Ordering:** Order utility classes logically: layout (`flex`, `grid`) -> positioning -> box model (`p-4`, `m-2`) -> typography -> backgrounds/borders -> states (`hover:`, `focus-visible:`).

## 4. JavaScript Standards

- Use native ES2022 modules (`import` / `export`), `const` by default, `let` only when reassignment is required, and never `var`.
- Bind event listeners via `data-*` attributes (`data-action="open-modal"`) rather than inline `onclick="..."` HTML attributes.
- Handle all `fetch()` errors gracefully with user-visible toast notifications.
