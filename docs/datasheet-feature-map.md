# Sales Fusion CRM Feature Map

This map links the product's primary differentiating features to their routes and user-facing views.

| Feature | Route(s) | View(s) | Implementation |
| --- | --- | --- | --- |
| Public lead capture and conversion | `GET/POST /capture/lead`; `POST /leads/{lead}/convert` | `resources/views/pages/leads/capture.blade.php`; `resources/views/pages/leads/show.blade.php` | `LeadCaptureController`; `ConvertLeadController` |
| Deal pipeline and stage movement | `GET /deals`; `PATCH /deals/{deal}/stage` | `resources/views/pages/deals/index.blade.php`; `resources/views/pages/deals/show.blade.php` | `DealController`; `MoveDealStageController` |
| Quotations with signed customer acceptance | `POST /quotations/{quotation}/send`; `GET /q/{quotation}`; `POST /q/{quotation}/accept`; `POST /q/{quotation}/reject` | `resources/views/pages/quotations/show.blade.php`; `resources/views/pages/quotations/public.blade.php` | `QuotationWorkflowController`; `PublicQuotationController` |
| Sales analytics and reports | `GET /reports` | `resources/views/pages/reports/index.blade.php` | `ReportController`; `AnalyticsService` |
| Mobile trade-show capture and event check-in | `GET/POST /mobile/capture`; `GET/POST /mobile/events` | `resources/views/pages/mobile/capture.blade.php`; `resources/views/pages/mobile/events.blade.php` | `Mobile/CaptureController`; `Mobile/EventController`; PWA manifest and service worker |

All mutation routes above are protected by the web authentication and role middleware defined in `routes/web.php`. The public quotation actions require signed URLs; public lead intake is throttled and includes a honeypot field.
