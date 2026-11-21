# How to Set Up Sale Fusion CRM

This guide walks through deploying **Sale Fusion CRM** (Laravel 13 · PHP 8.3+ · MySQL 8.0+ · Tailwind CSS v4 · Vite) on either a **Linux VPS** or **Docker**, logging in as the first administrator, and customizing your company branding.

---

## Option 1: VPS / Bare-Metal Linux Deployment (Ubuntu 22.04 / 24.04)

### 1. System Prerequisites

Install the required runtime packages on your server:

- **PHP 8.3+** with extensions: `php8.3-fpm`, `php8.3-mysql`, `php8.3-mbstring`, `php8.3-xml`, `php8.3-curl`, `php8.3-zip`, `php8.3-gd`, `php8.3-bcmath`, `php8.3-intl`, `php8.3-fileinfo`
- **Composer 2.x**
- **Node.js 20+ & npm** (for compiling Tailwind CSS v4 and ES2022 modules via Vite)
- **MySQL 8.0+** (`InnoDB`, `utf8mb4_unicode_ci`)
- **Nginx** (recommended) or **Apache 2.4** (`mod_rewrite` enabled)

### 2. Unpack Source & Install Dependencies

```bash
unzip sale-fusion-crm.zip -d /var/www/sales-crm
cd /var/www/sales-crm

# Install PHP dependencies (production)
composer install --no-dev --optimize-autoloader

# Install frontend dependencies and compile Vite / Tailwind CSS v4 assets
npm ci
npm run build
```

> **Note:** On a development workstation where you want to run `php artisan test`, run `composer install` (without `--no-dev`).

### 3. Configure Environment Variables

Copy the single environment template and generate your application key:

```bash
cp .env.example .env
php artisan key:generate
```

> **Security Notice:** `.env.example` ships with safe production defaults (`APP_ENV=production`, `APP_DEBUG=false`). Before running database migrations or seeders, configure your environment secrets:
> 1. `APP_KEY` (generated via `php artisan key:generate`)
> 2. `DB_PASSWORD` & `DB_ROOT_PASSWORD` (strong database passwords)
> 3. `CRM_ADMIN_PASSWORD` (password for primary administrator: `superadmin@salescrm.test`)
> 4. `CRM_DEMO_PASSWORD` (optional demo account password; auto-generated with 16 characters if blank)

Edit `.env` with your production values:

```dotenv
APP_NAME="Sale Fusion CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-crm-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_crm
DB_USERNAME=crm_user
DB_PASSWORD=replace-with-strong-db-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=replace-with-smtp-password
MAIL_FROM_ADDRESS="crm@your-crm-domain.com"

# Optional: generate a 64-char hex secret for POST /api/leads/capture
CRM_LEAD_WEBHOOK_SECRET=replace-with-random-hex-secret
```

### 4. Create the MySQL 8 Database & Load Schema

Create an empty MySQL 8 database and user:

```sql
CREATE DATABASE sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'replace-with-strong-db-password';
GRANT ALL PRIVILEGES ON sales_crm.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
```

Choose **either** Laravel migrations (recommended) or the pre-exported SQL files:

**Method A — Laravel Migrations + Seeder (recommended):**
```bash
# With sample Kanban deals, leads, contacts, quotations, and demo accounts:
php artisan migrate --force --seed

# Or roles & permissions only (blank workspace without demo deals/leads):
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
```

**Method B — Direct MySQL 8 Schema & Seed Import:**
```bash
# 1. Create all CRM tables, primary keys, foreign keys, and indexes from empty
mysql -u crm_user -p sales_crm < database/schema.sql

# 2. (Optional) Load sample roles, demo users, pipelines, deals, contacts, quotations & tasks
mysql -u crm_user -p sales_crm < database/seed.sql
```

### 5. Link Storage, Set Permissions & Cache Config

```bash
php artisan storage:link
mkdir -p storage/app/private/attachments storage/app/mpdf
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Configure Web Server (Nginx or Apache)

**Nginx (`/etc/nginx/sites-available/sales-crm`):**

```nginx
server {
    listen 80;
    server_name your-crm-domain.com;
    root /var/www/sales-crm/public;
    index index.php;

    client_max_body_size 15M;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site, reload Nginx, and attach HTTPS via Certbot (`sudo certbot --nginx -d your-crm-domain.com`).

**Apache (VirtualHost):**
Point `DocumentRoot` to `/var/www/sales-crm/public`, set `AllowOverride All` on `<Directory /var/www/sales-crm/public>` so `public/.htaccess` handles routing, and enable `a2enmod rewrite headers`.

### 7. Background Queue Worker & Scheduler

Add the Laravel scheduler cron entry for the `www-data` user (`crontab -e -u www-data`):

```cron
* * * * * cd /var/www/sales-crm && php artisan schedule:run >> /dev/null 2>&1
```

Run a Supervisor worker for queued quotation emails, outbound webhooks, and CSV import/export jobs:

```ini
[program:sale-fusion-crm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sales-crm/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sales-crm/storage/logs/worker.log
```

---

## Option 2: Docker Deployment (`docker-compose.yml`)

### 1. Prepare `.env`

Copy `.env.example` to `.env` and fill in the Docker database credentials:

```bash
cp .env.example .env
```

Set at minimum these variables in `.env`:

```dotenv
APP_URL=http://localhost:8080
APP_PORT=8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sales_crm
DB_USERNAME=crm_user
DB_PASSWORD=replace-with-strong-db-password
DB_ROOT_PASSWORD=replace-with-strong-root-password
```

### 2. Start PHP-FPM, Nginx, Background Queue Worker & MySQL 8

Run the initial Docker Compose build and application bootstrap:

```bash
# 1. Build Docker images with multi-stage entrypoint
docker compose build

# 2. Install dependencies & build frontend assets
docker compose run --rm app composer install --optimize-autoloader
docker compose run --rm app npm ci
docker compose run --rm app npm run build
docker compose run --rm app php artisan key:generate

# 3. Start all services (app, nginx, worker, db)
docker compose up -d

# 4. Run database migrations and seeders
docker compose exec app php artisan migrate --force --seed
docker compose exec app php artisan storage:link
```

> To load from the SQL files instead of running migrations, run:
> ```bash
> docker compose exec -T db mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < database/schema.sql
> docker compose exec -T db mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < database/seed.sql
> ```

Visit **http://localhost:8080/login** in your browser.

---

## First Admin Login & Security Checklist

When seeded via `DemoCrmSeeder` or `database/seed.sql`, the following accounts are available immediately:

### 1. Main Production Account
| Role | Email | Password | Default Scope |
| :--- | :--- | :--- | :--- |
| **Super Admin (Main)** | `superadmin@salescrm.test` | `Admin@Fusion2026!` *(or `CRM_ADMIN_PASSWORD` in `.env`)* | Full unrestricted system access, roles, users, webhooks, audit logs (no demo restrictions or banners). |

### 2. Demo Sandbox Accounts (Password: `password` or `CRM_DEMO_PASSWORD` in `.env`)
| Role | Email | Password | Default Scope |
| :--- | :--- | :--- | :--- |
| **Demo Super Admin** | `demosuperadmin@salescrm.test` | `password` | Full system access, roles, users, webhooks, audit logs (demo sandbox). |
| **Demo Org Admin** | `demoadmin@salescrm.test` | `password` | Organization-wide CRM, users, teams, webhooks (demo sandbox). |
| **Demo Sales Manager** | `demomanager@salescrm.test` | `password` | Enterprise Sales team records, discount approvals. |
| **Demo Sales Rep** | `demorep@salescrm.test` | `password` | Own leads, deals, quotations & tasks (Enterprise Sales). |
| **Demo SMB Rep** | `demosmbrep@salescrm.test` | `password` | Own records (SMB Inbound — isolated from Enterprise Sales). |
| **Demo Analyst** | `demoanalyst@salescrm.test` | `password` | Read-only dashboards, pipelines & reports. |

### Immediate Steps Before Production Use
1. Sign in as `superadmin@salescrm.test` and create your real administrator account under **Settings → Users & Teams** (`/settings/users`).
2. Deactivate or change passwords on all `*.salescrm.test` demo accounts.
3. Enable Email Two-Factor Authentication under **Settings → Security** (`/settings/security`).
4. Set a strong `CRM_LEAD_WEBHOOK_SECRET` in `.env` and configure outbound endpoints under **Settings → Webhooks** (`/settings/webhooks`).

### Production Environment Checklist

Before going live, ensure the following values are set in your production `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
SESSION_ENCRYPT=true
CRM_DEMO_PASSWORD=replace-with-a-strong-random-password
```

#### Prerequisites Summary

| Dependency | Minimum Version |
| :--- | :--- |
| **PHP** | 8.3+ (extensions: `mbstring`, `xml`, `curl`, `zip`, `gd`, `bcmath`, `intl`, `fileinfo`) |
| **Composer** | 2.x |
| **Node.js** | 20+ |
| **MySQL** | 8.0+ (`InnoDB`, `utf8mb4_unicode_ci`) |

#### Setup Order for a Clean Machine

1. `cp .env.example .env`
2. Set `DB_PASSWORD` and `DB_ROOT_PASSWORD` (required before Docker Compose)
3. `php artisan key:generate`
4. Create the MySQL database and user (see Section 4 above)
5. `php artisan migrate --force --seed`
6. `npm ci && npm run build`
7. `php artisan storage:link`
8. `php artisan config:cache && php artisan route:cache && php artisan view:cache`

---

## Customizing Branding (Name, Logo, Colours & Defaults)

### 1. Application Name
Change `APP_NAME` in `.env` (read by `config/app.php` and rendered across page titles, navigation headers, login screens, emails, and quotation PDFs):

```dotenv
APP_NAME="Acme Revenue CRM"
VITE_APP_NAME="${APP_NAME}"
```

Clear cached config after editing `.env`: `php artisan config:clear && php artisan config:cache`.

### 2. Logo & Favicons
- Replace the browser icons in `public/favicon.svg`, `public/favicon.ico`, and `public/apple-touch-icon.png` (loaded globally via `resources/views/components/crm/favicons.blade.php`).
- Replace the sidebar brand mark SVG in `resources/views/components/layouts/app.blade.php` and the split-screen login mark in `resources/views/pages/auth/login.blade.php`.
- Customize printable/PDF quotation headers in `resources/views/pages/quotations/print.blade.php` and `resources/views/pdf/quotation.blade.php`.

### 3. Theme Colours & Typography (Tailwind CSS v4)
All brand and pipeline stage colours live in `resources/css/app.css` inside the CSS-first `@theme` block (no `tailwind.config.js` is used):

```css
@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;

    --color-brand-50: oklch(0.97 0.014 254.6);
    --color-brand-100: oklch(0.93 0.032 255.5);
    --color-brand-500: oklch(0.60 0.21 262.8);
    --color-brand-600: oklch(0.55 0.215 262.8);
    --color-brand-700: oklch(0.48 0.205 263.3);
    --color-brand-900: oklch(0.32 0.125 265.5);
    --color-brand-950: oklch(0.22 0.095 266.0);
}
```

Update the `--color-brand-*` or `--color-stage-*` values and rebuild assets with:

```bash
npm run build
```

### 4. Currency, Country & Quotation Prefix
Adjust defaults in `.env` (mapped in `config/crm.php`):

```dotenv
CRM_DEFAULT_CURRENCY=USD
CRM_DEFAULT_COUNTRY=US
CRM_QUOTE_PREFIX=INV
CRM_QUOTE_DISCOUNT_THRESHOLD=15.00
CRM_QUOTE_PUBLIC_LINK_DAYS=30
```

---

## Verifying the Installation

Run the automated test suite and code-style check on any development or CI machine:

```bash
php artisan test --compact
vendor/bin/pint --test
```
