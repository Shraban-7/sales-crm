# Security Verification Checklist — Sales CRM

**Last Updated:** 2026-11-17  
**Implementation Status:** `Active Engineering Gate`  
**Related Docs:** `GEMINI.md`, `docs/security/security-policy.md`, `docs/prompts/security-audit-prompt.md`

## 1. Feature & Pull Request Security Gate

Every feature implemented in Google Gemini Antigravity IDE must pass all items below before merge:

### Authentication & Session
- [ ] All CRM routes are wrapped in `['auth', 'active_user']` middleware.
- [ ] Login, password reset, and CSV export endpoints have explicit `throttle:` rate limiters configured.
- [ ] Deactivated users (`is_active = false`) are immediately logged out and blocked on subsequent requests.

### Authorization & IDOR Prevention
- [ ] Every Controller method calls `$this->authorize('<ability>', $modelOrClass)`.
- [ ] Every index, search, report, and export query applies `->visibleTo($request->user())`.
- [ ] Foreign key inputs in `FormRequest` (`company_id`, `contact_id`, `deal_id`, `assigned_to_id`) validate tenant/team ownership (`Rule::exists(...)->where('team_id', $this->user()->team_id)`).
- [ ] Automated cross-team and cross-rep IDOR tests (`403 Forbidden`) exist and pass.

### Input Validation & Output Encoding
- [ ] All write endpoints use a dedicated `FormRequest` class and `$request->validated()`.
- [ ] No model uses `protected $guarded = []`.
- [ ] Zero occurrences of unescaped `{!! $userInput !!}` exist in `resources/views/`.
- [ ] Zero occurrences of interpolated variables inside `whereRaw()`, `selectRaw()`, or `orderByRaw()`.

### Files, Exports & Audit Trail
- [ ] Uploaded files are stored on the `private` disk with UUID/ULID filenames and strict MIME validation.
- [ ] CSV exports sanitize leading `=`, `+`, `-`, `@` characters against formula injection.
- [ ] Sensitive mutations (lead conversion, stage changes, quote approval, role changes, exports) create an `audit_logs` entry without logging plaintext passwords or tokens.
