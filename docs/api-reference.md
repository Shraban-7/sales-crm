# Sale Fusion CRM — API & Route Reference Manual

**Version:** 1.0.1  
**Last Updated:** 2026-11-21  
**Target Audience:** System Integrators, Frontend Developers, Security Auditors, and DevOps Operators

---

## 1. Overview & Architecture

Sale Fusion CRM provides three distinct API & interface integration layers:

1. **Inbound REST Webhooks (`/api/leads/capture`):** High-throughput, machine-to-machine lead ingestion secured via HMAC-SHA256 signatures, replay timestamp windows, and IP rate limiting.
2. **Public Signed Quotation Client Links (`/q/{quotation:ulid}`):** Frictionless, zero-login buyer proposal portals secured with 128-bit unguessable ULIDs and cryptographic HMAC-SHA256 URL signatures with expiration timestamps.
3. **Core Internal CRM Web Interface (`/` & `/settings/*`):** Rich Blade & Tailwind CSS v4 endpoints protected by session authentication, CSRF tokens, team data visibility scopes, and Spatie RBAC role/permission middleware (`role:Super Admin|Org Admin`, `can:*`).
4. **Outbound Event Webhooks:** Real-time event notifications (`deal.won`, `quote.accepted`, etc.) dispatched via background queue workers with SHA256 HMAC payload signatures and automated 3x exponential backoff retries.

---

## 2. Authentication & Authorization Matrix

| Layer | URL Pattern | Auth Mechanism | Primary Middleware / Protections | Permitted Roles / Callers |
| :--- | :--- | :--- | :--- | :--- |
| **Inbound Lead Capture** | `POST /api/leads/capture` | HMAC-SHA256 Signature Header | `throttle:60,1`, Replay Protection (`X-CRM-Timestamp` <= 300s) | External Web Forms, Landing Pages, Third-Party Portals |
| **Public Customer Quotations** | `GET /q/{quotation:ulid}` | Cryptographic Signed URL | `signed`, 26-char 128-bit ULID route-model binding | External Buyer / Customer (No login required) |
| **Quotation Acceptance** | `POST /q/{quotation:ulid}/accept` | Cryptographic Signed URL | `signed`, `throttle:10,1`, CSRF exempt | External Buyer / Customer |
| **Quotation Rejection** | `POST /q/{quotation:ulid}/reject` | Cryptographic Signed URL | `signed`, `throttle:10,1`, CSRF exempt | External Buyer / Customer |
| **Mobile PWA Capture** | `GET/POST /mobile/capture` | Session Cookie + CSRF | `auth`, `EnsureUserIsActive`, `can:leads.create` | Sales Rep, SMB Rep, Sales Manager, Org Admin, Super Admin |
| **Deals & Pipelines** | `/deals`, `/pipelines/kanban` | Session Cookie + CSRF | `auth`, `EnsureUserIsActive`, `HasVisibilityScope` | Sales Rep, SMB Rep, Sales Manager, Org Admin, Super Admin, Analyst |
| **Quotation Management** | `/quotations/*` | Session Cookie + CSRF | `auth`, `EnsureUserIsActive`, `QuotationPolicy` | Sales Rep, SMB Rep, Sales Manager, Org Admin, Super Admin |
| **System Administration** | `/settings/users`, `/settings/system`, `/settings/smtp`, `/settings/stages`, `/settings/webhooks`, `/settings/audit-logs` | Session Cookie + CSRF | `auth`, `role:Super Admin\|Org Admin`, `can:*`, `EnsureNotDemoMode` | Super Admin, Org Admin |

---

## 3. Inbound REST API: Lead Ingestion

### `POST /api/leads/capture`

Submits new prospect leads directly into the CRM from marketing websites, trade show portals, or custom intake forms.

#### Request Headers

| Header | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `Content-Type` | `string` | **Yes** | Must be `application/json`. |
| `X-CRM-Signature` | `string` | **Yes** (when secret set) | Lowercase HMAC-SHA256 hex digest of `"{timestamp}.{raw_body}"`. |
| `X-CRM-Timestamp` | `integer` | **Yes** (when secret set) | Current Unix timestamp in seconds. Replay window: max 300 seconds delta. |

#### Calculating the HMAC Signature

```php
// PHP Example
$secret = config('crm.lead_capture.webhook_secret');
$timestamp = time();
$payload = json_encode(['first_name' => 'John', 'email' => 'john@example.com']);
$signedPayload = "{$timestamp}.{$payload}";
$signature = hash_hmac('sha256', $signedPayload, $secret);

// Send headers:
// X-CRM-Timestamp: 1763750000
// X-CRM-Signature: 5a8e2b...
```

#### Request Body Parameters

```json
{
  "first_name": "Rahim",
  "last_name": "Chowdhury",
  "email": "rahim@bengallogistics.example.com",
  "phone": "+8801711000999",
  "company_name": "Bengal Logistics Ltd",
  "job_title": "Head of Operations",
  "source": "website",
  "priority": "high",
  "expected_value": 750000.00,
  "notes": "Looking for fleet management and quotation approval system.",
  "team_id": 1
}
```

| Parameter | Type | Validation Rules | Description |
| :--- | :--- | :--- | :--- |
| `first_name` | string | `required\|string\|max:100` | Prospect first name. |
| `last_name` | string | `nullable\|string\|max:100` | Prospect family name. |
| `email` | string | `nullable\|email:rfc\|max:191` | Prospect primary email address. |
| `phone` | string | `nullable\|string\|max:30` | Direct telephone or mobile. |
| `company_name` | string | `nullable\|string\|max:191` | Organization / company name. |
| `job_title` | string | `nullable\|string\|max:120` | Professional designation. |
| `source` | string | `nullable\|string\|in:website,referral,cold_call,trade_show,linkedin,whatsapp,organic_search,partner,other` | Attribution channel (defaults to `website`). |
| `priority` | string | `nullable\|string\|in:low,medium,high,urgent` | Initial lead priority (defaults to `medium`). |
| `expected_value` | numeric | `nullable\|numeric\|min:0` | Estimated deal opportunity value. |
| `notes` | string | `nullable\|string\|max:2000` | Captured enquiry text or initial conversation notes. |
| `team_id` | integer | `nullable\|integer\|exists:teams,id` | Specific sales team ID. If omitted, uses round-robin assignment. |

#### HTTP Responses

- **`201 Created`**: Lead accepted, scored, assigned, and saved.
  ```json
  {
    "success": true,
    "lead_id": 142,
    "owner_id": 7,
    "team_id": 1,
    "message": "Lead captured successfully."
  }
  ```
- **`401 Unauthorized`**: Missing or invalid HMAC signature.
  ```json
  { "error": "Invalid HMAC signature." }
  ```
- **`408 Request Timeout`**: Timestamp skew exceeds 300 seconds (replay attack protection).
  ```json
  { "error": "Timestamp outside the acceptable window (replay protection)." }
  ```
- **`422 Unprocessable Entity`**: Validation errors on submitted fields.
  ```json
  {
    "message": "The first name field is required.",
    "errors": { "first_name": ["The first name field is required."] }
  }
  ```
- **`429 Too Many Requests`**: Rate limit exceeded (limit: 60 requests per minute per IP).

---

## 4. Public Customer Quotation Link Flow

Clients receive a signed URL via email (`QuotationDeliveryMail`) to review and approve proposals online without requiring a CRM user account.

### URL Structure & Cryptographic Signing

Public links use 26-character, 128-bit ULIDs (`Str::ulid()`) combined with Laravel's cryptographic HMAC-SHA256 signature and expiration timestamp:

```
https://your-crm-domain.com/q/01M7YQ7WVYVCRX9X6B43E5QRR7?expires=1766348000&signature=a3f8c87...
```

- **ULID Properties:** 26 characters (Crockford Base32), 128-bit entropy, strictly unguessable, time-ordered.
- **HMAC-SHA256 Verification:** Validated by `Illuminate\Routing\Middleware\ValidateSignature` (`signed`). Any URL alteration (e.g. tampering with the ULID, expiration timestamp, or parameters) returns an instant `403 Invalid Signature`.
- **Draft Protection:** Draft quotations always return `404 Not Found`, even if an operator signs a URL before sending.

### Endpoints

#### 1. `GET /q/{quotation:ulid}`
- **Middleware:** `signed`
- **Description:** Renders the public client quotation page with company details, seller contact, line items, taxes, discounts, and terms. Dynamically embeds pre-signed `acceptUrl` and `rejectUrl` action buttons.

#### 2. `POST /q/{quotation:ulid}/accept`
- **Middleware:** `signed`, `throttle:10,1`
- **Parameters:**
  - `acceptance_name` (`required|string|max:150`): Full name of the authorized client signatory.
  - `acceptance_email` (`nullable|email:rfc|max:191`): Optional confirmation email address.
- **Workflow:** Updates quotation status to `accepted`, sets `accepted_at`, syncs linked deal revenue value, triggers an audit log, and notifies the account owner via in-app/email alerts.

#### 3. `POST /q/{quotation:ulid}/reject`
- **Middleware:** `signed`, `throttle:10,1`
- **Parameters:**
  - `rejection_reason` (`required|string|max:500`): Client's stated feedback or reason for declining.
- **Workflow:** Updates quotation status to `rejected`, records `rejection_reason`, updates `rejected_at`, triggers an audit log, and alerts the quote owner.

---

## 5. Mobile Progressive Web App (PWA) Endpoints

Sales reps attending trade shows and conferences can capture contacts on-site with camera card uploads and offline service-worker caching.

### `GET /mobile/capture`
- **Middleware:** `auth`, `EnsureUserIsActive`
- **Description:** Renders the responsive camera-ready card capture interface. Caches offline assets via `/public/sw.js`.

### `POST /mobile/capture`
- **Middleware:** `auth`, `EnsureUserIsActive`
- **Supported Enctypes:** `multipart/form-data` or `application/json`
- **Form Fields:**
  - `first_name` (`required|string|max:100`)
  - `last_name` (`nullable|string|max:100`)
  - `company_name` (`nullable|string|max:191`)
  - `job_title` (`nullable|string|max:120`)
  - `email` (`nullable|email:rfc|max:191`)
  - `phone` (`nullable|string|max:30`)
  - `event_id` (`nullable|integer|exists:events,id`)
  - `priority` (`nullable|string|in:low,medium,high,urgent`)
  - `card_photo` (`nullable|image|max:10240`): Photo captured via device camera (`capture="environment"`). Stored securely on the `private` disk under `cards/{team_id}/{uuid}.ext`.
  - `notes` (`nullable|string|max:2000`): Logged immediately as an initial activity note.

---

## 6. Outbound Webhooks Reference

Organizations can subscribe external services (Slack, ERP, accounting systems, Zapier) to real-time CRM lifecycle events.

### Managing Subscriptions
Administrators configure webhook endpoints at `/settings/webhooks`.

### Outbound Event List

| Event Name | Trigger Condition |
| :--- | :--- |
| `lead.created` | New lead created manually, via PWA, or captured through API. |
| `lead.converted` | Lead successfully converted into Company, Contact, and Deal. |
| `deal.created` | Opportunity created in any pipeline stage. |
| `deal.stage_changed` | Deal moved between pipeline stages (Kanban drag-and-drop or detail update). |
| `deal.won` | Deal moved to Closed Won stage. |
| `deal.lost` | Deal moved to Closed Lost stage with recorded lost reason. |
| `quote.sent` | Quotation sent to customer with signed public link. |
| `quote.accepted` | Customer or sales rep accepted proposal. |
| `quote.rejected` | Proposal declined by client. |

### Payload Structure & Verification

```json
{
  "event": "deal.won",
  "timestamp": "2026-11-21T14:30:00Z",
  "data": {
    "deal_id": 42,
    "title": "Enterprise Cloud Migration",
    "value": 650000.00,
    "currency": "BDT",
    "pipeline": "B2B Enterprise Pipeline",
    "owner": {
      "id": 7,
      "name": "Nadia Karim",
      "email": "nadia.karim@example.com"
    },
    "company": {
      "id": 12,
      "name": "Bengal Tech Holdings Ltd."
    }
  }
}
```

Each outbound request includes:
- `X-CRM-Signature: sha256={hmac}` computed using the webhook's pre-shared secret.
- Exponential backoff retry policy (3 automatic retries on HTTP 5xx or connection timeout).

---

## 7. Internal CRM Core Endpoints Reference

All routes below require standard session authentication (`auth`), an active user status (`EnsureUserIsActive`), and CSRF token protection.

### Leads (`/leads`)
- `GET /leads`: Paginated lead directory with search, team filters, and status filters.
- `POST /leads`: Store lead (`can:leads.create`).
- `GET /leads/{lead}`: Lead details, notes, activities, and duplicate candidate cards.
- `PUT /leads/{lead}`: Update lead record (`can:leads.update.own` / `team` / `all`).
- `DELETE /leads/{lead}`: Soft-delete lead (`can:leads.delete`).
- `POST /leads/{lead}/convert`: Atomic transaction converting lead to Company + Contact + Deal (`can:leads.convert`).
- `POST /leads/{lead}/merge`: Merge secondary duplicate lead into primary lead.
- `GET /leads/check-duplicates`: Live AJAX duplicate detector querying email, phone, and company matches.
- `GET /leads/export`: Streamed CSV export of authorized lead records (`can:exports.create.*`).

### Pipelines & Kanban (`/pipelines`)
- `GET /pipelines`: Directory of sales pipelines.
- `GET /pipelines/kanban`: Active Kanban board with drag-and-drop deal cards, win probabilities, and stage totals.
- `GET /pipelines/{pipeline}/kanban`: Kanban board for specific pipeline.
- `POST /pipelines/{pipeline}/stages`: Create pipeline stage (`can:pipelines.manage`).
- `POST /pipelines/{pipeline}/stages/reorder`: Persist drag-and-drop stage ordering (`can:pipelines.manage`).
- `DELETE /pipelines/{pipeline}/stages/{stage}`: Remove unused stage (`can:pipelines.manage`).

### Deals (`/deals`)
- `GET /deals`: Paginated deals directory.
- `POST /deals`: Store new deal opportunity (`can:deals.create`).
- `GET /deals/{deal}`: Deal workspace with stage history, quotations, timeline, and weighted forecast.
- `PUT /deals/{deal}`: Update deal parameters (`can:deals.update.*`).
- `PATCH /deals/{deal}/stage`: Move stage mutation with stage-entry validations and lost reason dialog (`can:deals.move_stage.own`).
- `DELETE /deals/{deal}`: Soft delete deal (`can:deals.delete`).
- `GET /urgent-attention`: Filtered view of rotting deals, overdue tasks, and pending follow-ups.

### Quotations (`/quotations`)
- `GET /quotations`: Quotation register with version tracking and approval flags.
- `GET /quotations/create`: Interactive quotation builder with live line-item totals calculator.
- `POST /quotations`: Save draft quotation (`can:quotations.create`).
- `GET /quotations/{quotation}`: Quotation detail with PDF preview and client link generator.
- `PUT /quotations/{quotation}`: Update draft quotation line items (`can:quotations.update.own`).
- `POST /quotations/{quotation}/send`: Validate discount threshold, transition to `Sent`, queue PDF email, and issue signed client link (`can:quotations.send`).
- `POST /quotations/{quotation}/accept`: Record internal manual acceptance.
- `POST /quotations/{quotation}/reject`: Record internal rejection with reason.
- `POST /quotations/{quotation}/approve-discount`: Manager sign-off for quotes exceeding 15% discount threshold (`can:quotations.approve_discount`).
- `POST /quotations/{quotation}/clone`: Version clone branching a new draft revision from rejected/expired quotes (`can:quotations.create`).
- `GET /quotations/{quotation}/print`: Clean, print-ready proposal and PDF export layout.

### Administrative Configuration (`/settings/*`)
Gated by role: `Route::middleware(['role:Super Admin|Org Admin'])`.
- `GET/POST/PUT /settings/users`: User management, invite accounts, role assignments, and team reassignments.
- `POST /settings/teams`: Create sales teams and configure monthly revenue quotas.
- `GET/POST /settings/stages`: In-app pipeline stage editor (win probabilities, rotting day thresholds, colors).
- `GET/POST /settings/smtp`: Custom SMTP mail transport credentials and 1-click test email dispatch.
- `GET/PUT /settings/system`: General branding, currency, timezone, discount threshold, and operational policies.
- `GET/POST/PUT/DELETE /settings/webhooks`: Outbound webhook endpoints and subscription events.
- `GET /settings/audit-logs`: Full audit trail of security and record mutations with before/after diffs.
