# Application Directory Structure — Sales CRM

**Last Updated:** 2026-11-17  
**Implementation Status:** `Proposed (Greenfield Blueprint)`  
**Related Docs:** `GEMINI.md`, `docs/architecture/system-architecture.md`, `docs/architecture/coding-standards.md`

## 1. Overview

The project follows the streamlined [Laravel 12 Configuration](https://laravel.com/framework/docs/12.x/configuration) directory structure (introduced in Laravel 11/12 with `bootstrap/app.php` routing and middleware registration) augmented with dedicated domain directories (`app/Actions`, `app/Data`, `app/Enums`, `app/Services`, `app/Policies`).

## 2. Canonical Directory Tree (`Proposed`)

```text
sales-crm/
├── app/
│   ├── Actions/                  # Single-responsibility atomic use-cases
│   │   ├── Deals/
│   │   │   └── MoveDealStageAction.php
│   │   ├── Leads/
│   │   │   └── ConvertLeadToDealAction.php
│   │   └── Quotations/
│   │       └── CalculateQuotationTotalsAction.php
│   ├── Console/
│   │   └── Commands/             # Scheduled CRM commands (reminders, rotting deals)
│   ├── Data/                     # Immutable readonly PHP 8.3 DTOs
│   ├── Enums/                    # Backed string/int Enums (LeadStatus, DealStatus, etc.)
│   ├── Events/                   # Domain events (LeadConverted, DealStageChanged)
│   ├── Exceptions/               # Domain-specific exceptions
│   ├── Http/
│   │   ├── Controllers/          # Thin Resource & Single-Action Controllers
│   │   ├── Middleware/           # EnsureActiveUser, SetTeamContext, SecurityHeaders
│   │   └── Requests/             # FormRequest validation classes per module
│   ├── Jobs/                     # Queued jobs (ProcessCsvImportJob, SendFollowUpDigestJob)
│   ├── Listeners/                # Event listeners (RecordAuditLogListener, NotifyDealOwner)
│   ├── Mail/                     # Mailable classes (QuotationSentMail, ReminderDueMail)
│   ├── Models/                   # Eloquent ORM models & Scopes
│   │   └── Scopes/               # VisibleToUserScope
│   ├── Notifications/            # Database & Mail notification classes
│   ├── Observers/                # Audit & cache-invalidation observers
│   ├── Policies/                 # Authorization policies per model
│   ├── Providers/                # AppServiceProvider (Gates, strict mode, macros)
│   └── Services/                 # Multi-step domain orchestration services
│       ├── Analytics/
│       ├── Imports/
│       ├── Leads/
│       ├── Pipelines/
│       └── Quotations/
├── bootstrap/
│   ├── app.php                   # Laravel 12 middleware, exception, and route bootstrapper
│   └── providers.php
├── config/
│   ├── crm.php                   # Custom CRM business thresholds & defaults
│   └── permission.php            # Spatie Laravel Permission configuration
├── database/
│   ├── factories/                # Model factories for Pest/PHPUnit testing
│   ├── migrations/               # Ordered schema migrations
│   └── seeders/                  # RolePermissionSeeder, DefaultPipelineSeeder, DemoCrmSeeder
├── docs/                         # Complete Markdown engineering documentation suite
├── public/                       # Web server document root
├── resources/
│   ├── css/
│   │   └── app.css               # Tailwind CSS v4 (@import "tailwindcss"; @theme {...})
│   ├── js/
│   │   ├── app.js                # Entry point
│   │   └── modules/              # Scoped Vanilla JS modules (kanban.js, quote-calc.js)
│   └── views/
│       ├── components/           # Reusable Blade UI components (<x-ui.button>, <x-crm.badge>)
│       ├── layouts/              # App shell, guest shell, print layout
│       └── pages/                # Module views (leads/, contacts/, companies/, deals/, etc.)
├── routes/
│   ├── console.php               # Scheduled tasks definition
│   └── web.php                   # Authenticated web & JSON fragment routes
├── storage/
│   └── app/
│       └── private/              # Protected CRM attachments, imports, generated PDFs
├── tests/
│   ├── Feature/                  # HTTP, Database, Policy, and Workflow tests
│   └── Unit/                     # Pure domain calculation, Enum, and Action unit tests
├── GEMINI.md                     # Antigravity IDE agent instructions
├── PROJECT_STATUS.md             # Module implementation tracker
├── composer.json
├── package.json
└── vite.config.js
```

## 3. Placement Rules for Gemini Antigravity

| Artifact Type | Target Location | Naming Pattern |
| :--- | :--- | :--- |
| Eloquent Model | `app/Models/` | Singular PascalCase (`Lead.php`, `DealStageHistory.php`) |
| Enum | `app/Enums/` | `<Entity><Field>Enum` or `<Domain><Concept>` (`LeadStatus.php`) |
| FormRequest | `app/Http/Requests/<Module>/` | `Store<Entity>Request.php`, `Update<Entity>Request.php` |
| Controller | `app/Http/Controllers/<Module>/` | `<Entity>Controller.php` |
| Service | `app/Services/<Module>/` | `<Entity>Service.php` |
| Action | `app/Actions/<Module>/` | `<Verb><Noun>Action.php` |
| Policy | `app/Policies/` | `<Entity>Policy.php` |
