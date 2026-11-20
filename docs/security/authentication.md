# Authentication & Session Security — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/security/security-policy.md`, `docs/modules/users-teams-and-permissions.md`, `docs/adr/ADR-003-authentication-and-authorization.md`

## 1. Authentication Architecture

Authentication uses Laravel's stateful `web` session guard backed by the `database` (or `redis`) session driver as configured in [Laravel 13 Configuration](https://laravel.com/docs/13.x/configuration).

```mermaid
sequenceDiagram
    actor User
    participant Ctrl as AuthenticatedSessionController
    participant RL as RateLimiter
    participant Auth as Laravel Auth Guard
    participant DB as MySQL (users & sessions)
    participant Aud as AuditLogger

    User->>Ctrl: POST /login (email, password, _token)
    Ctrl->>RL: Check throttle key (strtolower(email) + '|' + IP)
    RL-->>User: 429 Too Many Requests (if > 5 attempts / 60s)
    Ctrl->>Auth: Attempt credentials + check is_active = true
    alt Invalid Credentials or Inactive User
        Auth->>RL: Increment failed hit counter
        Auth->>Aud: Log 'login_failed' in audit_logs
        Auth-->>User: 422 Validation Error (generic message)
    else Valid & Active User
        Auth->>Ctrl: Regenerate Session ID ($request->session()->regenerate())
        Ctrl->>DB: Update last_login_at & last_login_ip
        Ctrl->>Aud: Log 'login_succeeded' in audit_logs
        Ctrl-->>User: 302 Redirect to Dashboard
    end
```

## 2. Password & Credential Standards

- **Hashing Algorithm:** `bcrypt` (cost factor `12`) or `argon2id` via `config/hashing.php`.
- **Password Complexity Rule:** Enforced in `AppServiceProvider` via `Password::defaults()`:
  ```php
  Password::defaults(function () {
      return Password::min(12)
          ->letters()
          ->mixedCase()
          ->numbers()
          ->symbols()
          ->uncompromised();
  });
  ```
- **Session Protection (`config/session.php`):**
  - `SESSION_DRIVER=database`
  - `SESSION_LIFETIME=120` (2 hours idle timeout)
  - `SESSION_ENCRYPT=false` (default; set `SESSION_ENCRYPT=true` in production `.env` for encrypted session payloads)
  - `SESSION_HTTP_ONLY=true` (`http_only => true` prevents JS `document.cookie` theft)
  - `SESSION_SAME_SITE=lax`
  - `SESSION_SECURE_COOKIE=true` (in production HTTPS)

> **Production Recommendation:** Set `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_ENCRYPT=true` in your production `.env` file. See the production checklist in `HowToSetup.md`.

## 3. Immediate Deactivation Enforcement (`EnsureUserIsActive`)

If an administrator sets `users.is_active = false`, the `EnsureUserIsActive` middleware runs on every authenticated web request:
```php
if ($request->user() && ! $request->user()->is_active) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    abort(403, 'Your account has been deactivated.');
}
```
