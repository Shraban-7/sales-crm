# Environment Variables Specification (`.env.example`) — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Engineering Specification)`  
**Related Docs:** `docs/deployment/deployment-guide.md`, `docs/security/authentication.md`

## 1. Environment Variable Governance

Per [Laravel 12 Configuration](https://laravel.com/framework/docs/12.x/configuration), `.env` files must **never** be committed to Git. Application code must never call `env()` directly outside `config/*.php` so that `php artisan config:cache` functions reliably in production.

## 2. Canonical `.env.example` Template

```ini
APP_NAME="Sale Fusion CRM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Dhaka
APP_URL=https://crm.example.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=daily
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DAYS=30

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_crm
DB_USERNAME=sales_crm_user
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=private

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@crm.example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Custom Sale Fusion CRM Business Configuration (mapped in config/crm.php)
CRM_DEFAULT_CURRENCY=BDT
CRM_QUOTE_NUMBER_PREFIX=QT
CRM_DEFAULT_DEAL_ROTTING_DAYS=14
CRM_MAX_DISCOUNT_WITHOUT_APPROVAL=15.00
CRM_AUDIT_LOG_RETENTION_DAYS=365
```
