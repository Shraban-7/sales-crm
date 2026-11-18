# Input Validation, Mass Assignment & Injection Prevention — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/security-policy.md`, `docs/architecture/backend-architecture.md`

## 1. Mandatory `FormRequest` Validation

Every state-changing HTTP endpoint (`POST`, `PUT`, `PATCH`) must type-hint a dedicated `FormRequest` subclass. Controllers must **never** pass `$request->all()` or `$request->input()` into Eloquent `create()` or `update()` calls—always use `$request->validated()` or convert `$request->validated()` into a typed DTO (`app/Data/`).

## 2. Mass-Assignment Protection Rules

1. **Strict `$fillable` Whitelists:** Every Eloquent model must explicitly list user-editable attributes in `protected $fillable = [...]`.
2. **Privileged Columns Excluded from Mass Assignment:** Security-sensitive or workflow-governed fields (`team_id`, `converted_at`, `converted_deal_id`, `quote_number`, `subtotal`, `grand_total`, `is_active`) must be assigned explicitly inside Domain Actions/Services rather than accepted blindly from request arrays.
3. **Strict Mode Enabled:** `Model::preventSilentlyDiscardingAttributes(! app()->isProduction())` ensures any unexpected attribute passed to `fill()` throws a `MassAssignmentException` in development and test runs.

## 3. SQL Injection Prevention on Dynamic Sorting & Filtering

When index tables accept `sort_by` and `sort_dir` query parameters, validate them against an explicit allowlist before passing them to Eloquent:

```php
$allowedSorts = ['created_at', 'value', 'expected_close_date', 'title'];
$sortBy = in_array($request->query('sort_by'), $allowedSorts, true)
    ? $request->query('sort_by')
    : 'created_at';

$sortDir = $request->query('sort_dir') === 'asc' ? 'asc' : 'desc';

$query->orderBy($sortBy, $sortDir);
```

## 4. XSS & HTML Output Escaping

- Always render dynamic data in Blade using double curly braces `{{ $lead->company_name }}`.
- Never use `{!! $activity->body !!}` on user-supplied text. To preserve line breaks in plain-text notes safely, use `{!! nl2br(e($activity->body)) !!}` or format via safe CSS `whitespace-pre-line` (`<p class="whitespace-pre-line">{{ $activity->body }}</p>`).
