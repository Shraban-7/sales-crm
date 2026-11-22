# Sale Fusion CRM — User Guide, Live Demo & Platform Documentation

**Live / Production Domain:** [`https://sales-crm.devshraban.com`](https://sales-crm.devshraban.com)  
**Built With:** PHP 8.3+ · Laravel 13 · MySQL 8.0 · Blade · Tailwind CSS v4 · Vanilla JS · mPDF  
**Project Status:** Complete across all `15` CRM modules (`19` database migrations, automated PHPUnit suite & Laravel Pint style checks verified — see [PROJECT_STATUS.md](PROJECT_STATUS.md) and [HowToSetup.md](HowToSetup.md))  
**Walkthrough Video:** 30-Second 1080p Voiceover Demo available at [`docs/screenshots/sale-fusion-crm-demo.mp4`](docs/screenshots/sale-fusion-crm-demo.mp4)

![Sale Fusion CRM Executive Dashboard](docs/screenshots/03-dashboard.png)

---

## Table of Contents

1. [What Is Sale Fusion CRM? (Plain-English Overview)](#1-what-is-sale-fusion-crm-plain-english-overview)
2. [Demo Site Access & Login Credentials](#2-demo-site-access--login-credentials)
3. [Everyday User Guide (Step-by-Step Tutorial)](#3-everyday-user-guide-step-by-step-tutorial)
4. [Visual Tour of Every Screen (`28` Pages)](#4-visual-tour-of-every-screen-28-pages)
5. [How the Technology Works (Business & Technical View)](#5-how-the-technology-works-business--technical-view)
6. [Hosting on `sales-crm.devshraban.com` & Local Setup](#6-hosting-on-sales-crmdevshrabancom--local-setup)
7. [Complete Engineering Documentation Index](#7-complete-engineering-documentation-index)
8. [Licence](#8-licence)

---

## 1. What Is Sale Fusion CRM? (Plain-English Overview)

**Sale Fusion CRM** is an all-in-one web workspace built for **B2B sales teams and agencies** to capture new customer enquiries across 20 lead sources, track every conversation and deal on a visual drag-and-drop Kanban board, send formal price quotes that buyers can approve online, integrate with external tools via HMAC-signed inbound & outbound webhooks, and monitor real revenue in real time—without paying per-user monthly subscription fees.

### What Problems Does It Solve?
- **No More Lost Enquiries:** Website forms, external landing pages, and HMAC-signed webhooks (`POST /api/leads/capture`) send prospects straight into the CRM across `20` structured lead sources, automatically score their readiness (`0–100`), spot duplicate entries, and assign the right sales representative.
- **Instant Pipeline Visibility:** Managers and reps see every deal organized by stage (*Prospecting → Qualification → Demo → Quote Sent → Negotiation → Won/Lost*) and spot stalled ("rotting") deals before revenue slips away.
- **Faster Quote Approvals & Online Sign-Off:** Sales reps build multi-line quotes with taxes and discounts. If a discount goes over your company limit (`15%` by default), a manager must approve it before the quote goes out. Buyers receive a secure private link where they can review and click **Accept** or **Decline** online.
- **Admin-Gated Webhooks & Integrations:** Administrators manage outbound event webhooks under **Settings → Webhooks** (`/settings/webhooks`) with encrypted HMAC-SHA256 signing secrets and delivery status tracking for `lead.created`, `lead.converted`, `deal.stage_moved`, `deal.won`, `deal.lost`, and `quotation.sent`.
- **Clear Accountability & Data Safety:** Each team member only sees what their role permits (e.g., Sales Reps see their own deals, Sales Managers see their team, Executives see everything), and every important change is permanently recorded in an uneditable audit history.

---

## 2. Login Credentials & Accounts

**Site URL:** [`https://sales-crm.devshraban.com`](https://sales-crm.devshraban.com) *(or your local server when testing offline)*

### Main Administrator Account
This is the primary production administrator account with **no demo mode restrictions** and zero demo banners:

| Role | Who Uses This | Email Address | Password | What They Can See & Do |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin (Main)** | System Owner / Primary Admin | `superadmin@salescrm.test` | Set `CRM_ADMIN_PASSWORD` before seeding. | Full unrestricted access to every record, system setting, user management, audit logs, and configuration. |

### Demo Showcase Accounts
When seeded with sample data (`DemoCrmSeeder` or `database/seed.sql`), you can sign in using any of the **6 role accounts** below:

| Job Role | Who Uses This | Email Address | Password | What They Can See & Do |
| :--- | :--- | :--- | :--- | :--- |
| **Demo Super Admin** | System Owner / IT Head | `demosuperadmin@salescrm.test` | Set `CRM_DEMO_PASSWORD` before seeding. | Full access to every record, pipeline configuration, user provisioning, audit logs, and system settings. |
| **Demo Org Admin** | Operations / RevOps Lead | `demoadmin@salescrm.test` | Set `CRM_DEMO_PASSWORD` before seeding. | Organization-wide access across all teams, users, companies, deals, quotations, imports, and audit logs. |
| **Demo Sales Manager** | Enterprise Team Lead (*Tariq Rahman*) | `demomanager@salescrm.test` | Set `CRM_DEMO_PASSWORD` before seeding. | Sees and manages all leads, deals, tasks, follow-ups, and high-discount quote approvals for the **Enterprise Sales** team. |
| **Demo Sales Rep** | Enterprise Rep (*Nadia Karim*) | `demorep@salescrm.test` | Set `CRM_DEMO_PASSWORD` before seeding. | Works on her own assigned leads, companies, contacts, deals, tasks, follow-ups, and draft quotes (cannot view other teams' data). |
| **Demo Sales Rep (Team B)** | SMB Rep (*Imran Hossain*) | `demosmbrep@salescrm.test` | Set `CRM_DEMO_PASSWORD` before seeding. | Works exclusively inside the **SMB Inbound** team (useful to verify team data isolation). |
| **Demo Read-Only Analyst** | Finance / BI Analyst (*Farhana Yasmin*) | `demoanalyst@salescrm.test` | Set `CRM_DEMO_PASSWORD` before seeding. | Views dashboards, reports, pipelines, and records without permission to create, edit, or delete business data. |

> **Interactive Demo Mode:** Demo mode defaults to `false` for a clean corporate installation. You can enable demo mode and login pre-fill showcase anytime directly from the database (`UPDATE system_settings SET value = '1' WHERE `key` = 'demo_mode';`) or via the in-app **System Settings** page (`/settings/system`).

### Quick 3-Minute Guided Demo (Try It Yourself)
1. Go to **`/login`** and sign in as **`demomanager@salescrm.test`** (password: configured in `CRM_DEMO_PASSWORD` in `.env` or shown upon seeding) so you can see team oversight + discount approvals.
2. Open **Leads** in the left menu and click **`Kamrul Hasan`** (*Apex Textiles BD*, score `82/100`).
3. Click **Convert to Deal** to turn him into a live Company, Contact, and Opportunity in one click.
4. Open **Pipeline Kanban** and drag a deal card between columns—notice totals and stage counts update automatically.
5. Open **Quotations** → click **`QT-2026-00001`** → click **Customer Link** in a new tab to see the live buyer acceptance page (`/q/...`).
6. Open **Reports** and set the **To** date a month ahead to inspect open pipeline bars, won revenue by currency, and the rep leaderboard.

---

## 3. Everyday User Guide (Step-by-Step Tutorial)

This section explains how a non-technical team uses Sale Fusion CRM day to day.

### Step 1: Signing In & Turning On 2-Step Verification
1. Visit [`https://sales-crm.devshraban.com/login`](https://sales-crm.devshraban.com/login) and sign in with your work email and password (use the **eye icon** if you want to check what you typed).
2. Click **Security** at the bottom of the left menu (`/settings/security`).
3. To protect your account with a one-time 6-digit email code at login, enter your current password and click **Enable Two-Factor Authentication**. You can also review your most recent login time and IP address here.

### Step 2: Capturing New Customer Enquiries (Leads)
- **Public Contact Form:** Anyone visiting [`https://sales-crm.devshraban.com/capture/lead`](https://sales-crm.devshraban.com/capture/lead) can fill out *Talk to our sales team*. New enquiries automatically enter the CRM and are assigned to an active sales rep in rotation.
- **Adding a Lead Manually:** Click **Leads** in the sidebar, type the prospect's name, company, email/phone, and source in **Quick Add Lead**, and click **Add Lead**. The system automatically assigns a `0–100` priority score.
- **Avoiding Double Work:** When opening a lead (`/leads/{id}`), if another lead shares the same email or phone number, a yellow alert highlights the duplicate so you can click **Merge into this lead**.

### Step 3: Turning a Qualified Lead Into a Deal
1. When a prospect is ready to discuss a purchase, open their profile in **Leads**.
2. Click the green **Convert to Deal** button at the top right.
3. Confirm the **Company Name**, **Deal Title**, **Initial Stage**, and **Expected Value**, then click **Convert**.
4. In one step, the CRM creates the **Company** record, the **Contact** person, and the **Deal** opportunity, linking all past notes and files together. *(Disqualified leads cannot be converted unless re-opened first.)*

### Step 4: Tracking Deals on the Visual Kanban Board
1. Click **Pipeline Kanban** in the left menu to see all deals arranged in vertical stage columns.
2. Simply **drag and drop** any deal card into the next column as talks move forward.
3. If you drag a deal into **Closed Lost**, the system asks you to enter a quick **Lost Reason** (e.g., *Budget cut* or *Chose competitor*) so leadership knows why deals are lost.
4. Click any deal title to see its full **Deal Summary** (`Value`, `Weighted Value`, `Probability`, `Expected Close`), **Stage History**, linked **Quotations**, and **Activity Timeline**.

### Step 5: Logging Calls, Tasks & Customer Follow-Ups
- **Activity Timeline:** Inside any Lead, Company, Contact, or Deal page, record a **Call**, **Email**, **Meeting**, or **Note**. Everyone on the team with permission sees a clean chronological history.
- **Tasks (`/tasks`):** Create action items linked to deals with priorities (*Low, Medium, High, Urgent*) and due times. Filter quickly between **Open**, **Overdue**, and **Completed**, and click **Complete** when finished.
- **Follow-Ups (`/follow-ups`):** View scheduled outreach neatly grouped into **Overdue**, **Today**, and **Upcoming**. After finishing a call, type your outcome notes and click **Done**—or expand **+ Schedule next follow-up** to book the next touchpoint immediately.

### Step 6: Creating Price Quotes & Getting Client Approval Online
1. Click **New Quotation** from a Deal page (or under **Quotations** in the sidebar).
2. Pick the deal, issue date, and expiration date, then add product/service lines (**Qty**, **Unit Price**, **Discount %**, **Tax %**). Totals update live as you type.
3. Click **Save Draft Quotation**. From the quote screen:
   - **High Discount Check:** If the overall discount exceeds `15%`, a **Sales Manager** or **Admin** sees an **Approve Discount** button that must be clicked before sending.
   - **Send to Customer:** Emails the customer a PDF proposal and activates a tamper-proof **Customer Link**.
   - **Buyer Online Experience:** Your client opens their private link (`/q/...`), reviews every line item, enters their name, and clicks **Accept Quotation** (or declines with a reason). Acceptance automatically updates the quote status and notifies your team.

### Step 7: Viewing Sales Reports, Importing Spreadsheets & Managing Teams
- **Reports (`/reports`):** Filter by date range, pipeline, team, or rep. Revenue totals are **always kept separate by currency** (`BDT`, `USD`, etc.) so exchange rates never distort totals.
- **Notifications (Bell Icon):** Watch the red badge on the top navbar bell icon for new assignments and overdue task reminders, and customize **In-App** vs **Email** alerts on `/notifications`.
- **Import / Export (`/imports`):** Upload `.csv` spreadsheets to bulk-add Leads, Contacts, or Companies (`Skip Duplicates` or `Update Duplicates`), or export authorized lists safely to Excel/CSV.
- **Users, Teams & Audit Logs (`/settings/users`, `/settings/audit-logs`):** Admins create users, organize sales teams with monthly revenue targets, reassign all active accounts cleanly when a rep leaves, and review before/after data diffs for compliance.

---

## 4. Visual Tour of Every Screen (`28` Pages)

All screenshots below were captured directly from the live application.

### A. Sign-In, Security & Public Customer Pages

| Split-Screen Sign In (`/login`) | Email Two-Factor Challenge (`/two-factor`) |
| :---: | :---: |
| ![Login Page](docs/screenshots/01-login.png) | ![Two-Factor Verification](docs/screenshots/27-two-factor-challenge.png) |
| Clean dark-and-light sign-in layout with password show/hide toggle and rate-limit protection. | Optional 6-digit email verification code challenge before opening the dashboard. |

| Public Talk-to-Sales Form (`/capture/lead`) | Signed Customer Quotation Portal (`/q/{ulid}`) |
| :---: | :---: |
| ![Public Lead Capture](docs/screenshots/02-lead-capture.png) | ![Signed Public Quotation](docs/screenshots/20-quotation-public.png) |
| Standalone public enquiry page with spam protection that auto-assigns incoming leads. | Private expiring client link to inspect line items and accept or decline the proposal online. |

---

### B. Dashboard, Mobile View & CRM Directories

| Executive Dashboard (`/`) | Mobile Responsive Dashboard (`390×844`) |
| :---: | :---: |
| ![Executive Dashboard](docs/screenshots/03-dashboard.png) | ![Mobile Dashboard](docs/screenshots/28-mobile-dashboard.png) |
| Instant snapshot of open pipeline value, won revenue, active leads, due tasks, and upcoming follow-ups. | Smartphone layout with slide-out drawer navigation and single-column cards. |

| Leads Directory (`/leads`) | Lead Profile & Conversion (`/leads/{id}`) |
| :---: | :---: |
| ![Leads Management](docs/screenshots/04-leads-index.png) | ![Lead Detail](docs/screenshots/05-lead-detail.png) |
| Search/filter leads, see `0–100` readiness bars, and quick-add prospects in two clicks. | Full prospect profile, attachment uploads, activity timeline, and Convert to Deal dialog. |

| Companies Directory (`/companies`) | Company Account Profile (`/companies/{id}`) |
| :---: | :---: |
| ![Companies Directory](docs/screenshots/06-companies-index.png) | ![Company Detail](docs/screenshots/07-company-detail.png) |
| Account directory displaying industry, city/country, contact counts, and active deal counts. | Rolls up company open pipeline, won revenue, subsidiaries, deals, and communication history. |

| Contacts Directory (`/contacts`) | Contact Profile (`/contacts/{id}`) |
| :---: | :---: |
| ![Contacts Directory](docs/screenshots/08-contacts-index.png) | ![Contact Detail](docs/screenshots/09-contact-detail.png) |
| Highlights primary **Decision Makers** and **Do Not Contact** compliance tags. | Contact details, quick editor modal, private document uploads, and conversation notes. |

---

### C. Pipelines, Deals, Tasks, Follow-Ups & Quotations

| Pipeline Kanban Board (`/pipelines/kanban`) | Pipelines & Stage Configuration (`/pipelines`) |
| :---: | :---: |
| ![Pipeline Kanban](docs/screenshots/10-pipelines-kanban.png) | ![Pipelines Config](docs/screenshots/11-pipelines-config.png) |
| Drag-and-drop deal cards across color-coded stages with real-time stage totals and probabilities. | Customize pipelines, reorder stages, set win probabilities, and tune deal rotting day limits. |

| Deals Directory (`/deals`) | Deal Workspace (`/deals/{id}`) |
| :---: | :---: |
| ![Deals Directory](docs/screenshots/12-deals-index.png) | ![Deal Detail](docs/screenshots/13-deal-detail.png) |
| Search deals by stage/status with quick deal creation and probability weighting. | Interactive top stage bar, weighted revenue calculation, stage history, quotes, and timeline. |

| Tasks & Reminders (`/tasks`) | Customer Follow-Ups (`/follow-ups`) |
| :---: | :---: |
| ![Tasks and Reminders](docs/screenshots/14-tasks.png) | ![Customer Follow-Ups](docs/screenshots/15-follow-ups.png) |
| Filterable action queue for Open, Overdue, and Completed deal deliverables. | Three-column urgency view (*Overdue*, *Today*, *Upcoming*) with inline next-step scheduling. |

| Quotations List (`/quotations`) | Interactive Quotation Builder (`/quotations/create`) |
| :---: | :---: |
| ![Quotations Index](docs/screenshots/16-quotations-index.png) | ![Quotation Builder](docs/screenshots/17-quotations-create.png) |
| Lists all quote versions, totals, validity dates, and current buyer decision status. | Live browser calculator for unlimited line items, discounts, and taxes. |

| Quotation Review & Approval (`/quotations/{id}`) | Print-Ready Proposal View (`/quotations/{id}/print`) |
| :---: | :---: |
| ![Quotation Detail](docs/screenshots/18-quotation-detail.png) | ![Printable Quotation](docs/screenshots/19-quotation-print.png) |
| Send PDF emails, manage discount approvals, copy client links, or clone rejected/expired versions. | Crisp print sheet and downloadable PDF layout for client procurement files. |

---

### D. Reports, Notifications, Data Operations & Security Controls

| Executive Reports (`/reports`) | Notifications & Channel Preferences (`/notifications`) |
| :---: | :---: |
| ![Executive Reports](docs/screenshots/21-reports.png) | ![Notifications Center](docs/screenshots/22-notifications.png) |
| Date/team/rep filters, multi-currency totals, stage funnel bars, rep leaderboard, and lead source conversion. | Unread notification list paired with the navbar bell badge, plus In-App / Email alert controls. |

| CSV Import & Export Center (`/imports`) | Users & Sales Teams (`/settings/users`) |
| :---: | :---: |
| ![Data Import Export](docs/screenshots/23-imports.png) | ![Users and Teams](docs/screenshots/24-settings-users.png) |
| Upload CSV spreadsheets safely or export filtered CRM data with row tracking. | Manage users, roles, monthly team quotas, and bulk account handovers when staff change roles. |

| Immutable Security Audit Logs (`/settings/audit-logs`) | Account Security & 2FA Settings (`/settings/security`) |
| :---: | :---: |
| ![Audit Logs](docs/screenshots/25-settings-audit-logs.png) | ![Security Settings](docs/screenshots/26-settings-security.png) |
| Permanent before/after change log showing who did what, when, and from which IP address. | Self-service password-confirmed 2FA switch and recent session sign-in audit details. |

---

## 5. How the Technology Works (Business & Technical View)

### At a Glance: Why It Is Reliable & Secure
| Capability | What It Means in Plain English | How It Works Technically |
| :--- | :--- | :--- |
| **Role & Team Privacy** | Reps cannot peek at another team's clients or export private leads; analysts cannot alter deals. | Dual enforcement via `FormRequest` + Laravel Policies (`app/Policies/`) paired with `HasVisibilityScope` (`own`, `team`, `all`) on every list/search query. |
| **Exact Financial Math** | Currency calculations never suffer from rounding bugs or accidentally add `BDT` + `USD` together. | All money columns use `DECIMAL(15, 2)`; `CalculateQuotationTotalsAction` computes line totals server-side; `AnalyticsService` groups every metric strictly by `currency`. |
| **Safe All-or-Nothing Saves** | Converting a lead either creates Company + Contact + Deal together or cancels cleanly—never leaving half-created data. | Multi-table actions (`ConvertLeadToDealAction`, `CreateQuotationAction`, `ReassignUserRecordsAction`) run inside `DB::transaction()`. |
| **Tamper-Proof Audit Trail** | Nobody—not even an admin—can edit or delete security audit records after the fact. | `AuditLoggerService` records IP, user agent, and JSON before/after values into `AuditLog`, whose Eloquent boot hooks throw `ImmutableRecordException` on update/delete attempts. |
| **Safe File & CSV Handling** | Uploaded attachments stay off public URLs, and Excel/CSV exports cannot execute hidden spreadsheet formulas. | Attachments are stored privately and streamed via `AttachmentController`; `CsvSanitizer` neutralizes formula prefixes (`=`, `+`, `-`, `@`). |
| **Automated Background Jobs** | Due task alerts, overdue follow-up escalations, and expired quote updates happen automatically every day. | Laravel Scheduler (`routes/console.php`) runs `crm:dispatch-due-reminders`, `crm:escalate-overdue-follow-ups`, and `crm:expire-quotations`. |

### Architecture Flow

```mermaid
flowchart LR
    Browser["Web Browser (Blade + Tailwind v4 + Vanilla JS)"] -->|"HTTPS + CSRF"| MW["Security Layer (Login, Active User Check, Signed URLs)"]
    Webhook["External Website"] -->|"POST /api/leads/capture (Signed)"| WH["LeadWebhookController"]
    MW --> Req["Input Validation (FormRequest)"]
    Req --> Ctrl["Thin Controllers"]
    Ctrl --> Policy["Permission Check + Team Scope"]
    Ctrl --> Layer["Business Actions & Services (DB Transactions)"]
    WH --> Layer
    Layer --> DB[("MySQL 8.0 Database")]
    Layer --> Bg["Emails, mPDF Quotes, CSV Jobs & Audit Log"]
```

### Technical Architecture Highlights (For Developers & IT Teams)
- **Strict PHP 8.3+ Codebase:** `declare(strict_types=1);` with typed parameters/returns, PHP backed Enums (`app/Enums/`), explicit `$fillable` properties on all models, and `Model::shouldBeStrict(! app()->isProduction())` in `AppServiceProvider` to prevent N+1 lazy loading.
- **Zero-Bloat Frontend:** Server-rendered Blade components (`resources/views/components/`) styled via Tailwind CSS v4 `@theme` design tokens (`resources/css/app.css`) and tiny Vanilla ES2022 JS modules (`kanban.js`, `quotation-calculator.js`, `app.js`).
- **Lead Webhook API:** External forms can `POST /api/leads/capture` (`routes/api.php`, rate-limited `60/min`) signed via HMAC-SHA256 in the `X-CRM-Signature` header (`CRM_LEAD_WEBHOOK_SECRET`), automatically routed via `LeadAssignmentService` and checked for duplicates via `LeadDuplicateService`.
- **Quotation Engine & Public Links:** `QuotationPdfService` generates PDF attachments using `mpdf/mpdf`; `QuotationWorkflowController` generates expiring ULID signed links (`/q/{quotation:ulid}`) processed cleanly by `AcceptQuotationAction` and `RejectQuotationAction`.

---

## 6. Hosting on `sales-crm.devshraban.com` & Local Setup

### Production Hosting (`https://sales-crm.devshraban.com`)

**Server Requirements:** Ubuntu 24.04 LTS · Nginx 1.24+ (with TLS) · PHP 8.3+ FPM (`bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `xml`, `zip`) · MySQL 8.0+ · Node.js 20+ · Supervisor + Cron.

1. **Configure `.env` on the production server:**
   ```dotenv
   APP_NAME="Sale Fusion CRM"
   APP_ENV=production
   APP_KEY=base64:GENERATE_WITH_ARTISAN_KEY_GENERATE
   APP_DEBUG=false
   APP_URL=https://sales-crm.devshraban.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sales_crm
   DB_USERNAME=sales_crm_user
   DB_PASSWORD=STRONG_DATABASE_PASSWORD

   SESSION_DRIVER=database
   SESSION_ENCRYPT=true
   QUEUE_CONNECTION=database
   CACHE_STORE=database

   MAIL_MAILER=smtp
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-smtp-user
   MAIL_PASSWORD=your-smtp-password
   MAIL_FROM_ADDRESS="crm@devshraban.com"
   MAIL_FROM_NAME="Sale Fusion CRM"

   CRM_DEFAULT_CURRENCY=BDT
   CRM_DEFAULT_COUNTRY=BD
   CRM_QUOTE_DISCOUNT_THRESHOLD=15.00
   CRM_QUOTE_PUBLIC_LINK_DAYS=30
   CRM_LEAD_WEBHOOK_SECRET=GENERATE_A_LONG_RANDOM_SECRET
   ```

2. **Install dependencies, build frontend assets, migrate, and cache configuration:**
   ```bash
   composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
   npm ci && npm run build

   php artisan key:generate
   php artisan storage:link
   php artisan migrate --force
   php artisan db:seed --class=RolePermissionSeeder --force
   # Optional for demo instances only (provisions the demo accounts above):
   # php artisan db:seed --class=DemoCrmSeeder --force

   php artisan optimize
   ```

3. **Nginx Virtual Host (`/etc/nginx/sites-available/sales-crm.devshraban.com`):**
   ```nginx
   server {
       listen 80;
       server_name sales-crm.devshraban.com;
       return 301 https://$host$request_uri;
   }

   server {
       listen 443 ssl http2;
       server_name sales-crm.devshraban.com;

       root /var/www/sales-crm/public;
       index index.php;

       ssl_certificate     /etc/letsencrypt/live/sales-crm.devshraban.com/fullchain.pem;
       ssl_certificate_key /etc/letsencrypt/live/sales-crm.devshraban.com/privkey.pem;

       client_max_body_size 15M;

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

4. **Background Scheduler Cron (`crontab -e`) & Queue Worker (Supervisor):**
   ```cron
   * * * * * cd /var/www/sales-crm && php artisan schedule:run >> /dev/null 2>&1
   ```
   Run a Supervisor worker executing `php artisan queue:work --sleep=3 --tries=3` for async email delivery and CSV jobs.

### Local Development Setup & Verification

```bash
cp .env.example .env
# Update DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Verify formatting and run the full test suite (`40` feature tests covering every route, role boundary, webhook, and workflow):
```bash
vendor/bin/pint --test
php artisan test --compact
```

> For complete **VPS (Nginx / Apache + MySQL 8)**, **Docker (`docker-compose.yml`)**, **SQL import (`database/schema.sql` & `database/seed.sql`)**, and **branding customization** instructions, see [HowToSetup.md](HowToSetup.md).

---

## 7. Complete Engineering Documentation Index

### Setup, AI Tooling & Governance
- [HowToSetup.md](HowToSetup.md) — Full VPS & Docker installation, first admin login, and branding guide
- [CLAUDE.md](CLAUDE.md) — Architecture, directory layout, conventions, and test commands for AI coding tools
- [PROJECT_STATUS.md](PROJECT_STATUS.md) — Module readiness matrix across Phases 1–6
- [CHANGELOG.md](CHANGELOG.md) — Release history starting at `1.0.0`
- [CONTRIBUTING.md](CONTRIBUTING.md) — Git workflow and contribution guidelines
- [LICENSE-NOTE.md](LICENSE-NOTE.md) — Buyer usage and modification licence terms
- [docs/deployment/vibe96-listing.md](docs/deployment/vibe96-listing.md) — Marketplace listing details

### Architecture (`docs/architecture/`)
- [system-architecture.md](docs/architecture/system-architecture.md) · [application-structure.md](docs/architecture/application-structure.md) · [backend-architecture.md](docs/architecture/backend-architecture.md) · [frontend-architecture.md](docs/architecture/frontend-architecture.md) · [coding-standards.md](docs/architecture/coding-standards.md) · [design-patterns.md](docs/architecture/design-patterns.md) · [request-lifecycle.md](docs/architecture/request-lifecycle.md) · [scalability-strategy.md](docs/architecture/scalability-strategy.md)

### Database (`docs/database/`)
- [database-design.md](docs/database/database-design.md) · [database-relationships.md](docs/database/database-relationships.md) · [erd.md](docs/database/erd.md) · [migrations-and-indexes.md](docs/database/migrations-and-indexes.md) · [database-optimization.md](docs/database/database-optimization.md) · [data-integrity.md](docs/database/data-integrity.md)

### Functional Modules (`docs/modules/`)
1. [leads.md](docs/modules/leads.md) · 2. [contacts.md](docs/modules/contacts.md) · 3. [companies.md](docs/modules/companies.md) · 4. [sales-pipeline.md](docs/modules/sales-pipeline.md) · 5. [deals-and-opportunities.md](docs/modules/deals-and-opportunities.md) · 6. [tasks-and-reminders.md](docs/modules/tasks-and-reminders.md) · 7. [activities-and-communications.md](docs/modules/activities-and-communications.md) · 8. [customer-follow-ups.md](docs/modules/customer-follow-ups.md) · 9. [quotations-and-proposals.md](docs/modules/quotations-and-proposals.md) · 10. [reports-and-analytics.md](docs/modules/reports-and-analytics.md) · 11. [users-teams-and-permissions.md](docs/modules/users-teams-and-permissions.md) · 12. [notifications.md](docs/modules/notifications.md) · 13. [import-and-export.md](docs/modules/import-and-export.md) · 14. [audit-logs.md](docs/modules/audit-logs.md)

### Security, Testing, Debugging & Deployment
- **Security (`docs/security/`):** [security-policy.md](docs/security/security-policy.md) · [authentication.md](docs/security/authentication.md) · [authorization.md](docs/security/authorization.md) · [role-permission-matrix.md](docs/security/role-permission-matrix.md) · [data-protection.md](docs/security/data-protection.md) · [input-validation.md](docs/security/input-validation.md) · [file-upload-security.md](docs/security/file-upload-security.md) · [security-checklist.md](docs/security/security-checklist.md)
- **Testing & Debugging (`docs/testing/`, `docs/debugging/`):** [testing-strategy.md](docs/testing/testing-strategy.md) · [feature-testing.md](docs/testing/feature-testing.md) · [test-cases.md](docs/testing/test-cases.md) · [troubleshooting.md](docs/debugging/troubleshooting.md) · [error-handling.md](docs/debugging/error-handling.md)
- **Deployment & ADRs (`docs/deployment/`, `docs/adr/`):** [deployment-guide.md](docs/deployment/deployment-guide.md) · [environment-variables.md](docs/deployment/environment-variables.md) · [production-checklist.md](docs/deployment/production-checklist.md) · [queue-and-scheduler.md](docs/deployment/queue-and-scheduler.md) · [backup-and-recovery.md](docs/deployment/backup-and-recovery.md) · [ADR-001](docs/adr/ADR-001-application-architecture.md)–[ADR-005](docs/adr/ADR-005-business-logic-organization.md)

---

## 8. Licence

Commercial buyer usage, modification, and deployment rights are documented in [LICENSE-NOTE.md](LICENSE-NOTE.md). Underlying open-source framework and library packages retain their respective licences as pinned in `composer.lock` and `package-lock.json`.
