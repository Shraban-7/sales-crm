<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\DemoCrmSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AllPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(DemoCrmSeeder::class);
    }

    public function test_guest_and_public_pages_render_cleanly(): void
    {
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sale Fusion CRM')
            ->assertSee('Sign In to Workspace')
            ->assertSee('favicon.svg', false)
            ->assertSee('favicon.ico', false)
            ->assertSee('apple-touch-icon.png', false)
            ->assertSee('data-toggle-password', false)
            ->assertDontSee('data-demo-panel', false)
            ->assertDontSee('superadmin@salescrm.test')
            ->assertDontSee('&amp;amp;', false);

        $originalEnv = app()->environment();
        app()->instance('env', 'local');
        try {
            $this->get(route('login'))
                ->assertOk()
                ->assertSee('Sign In to Workspace')
                ->assertDontSee('superadmin@salescrm.test')
                ->assertDontSee('Local Demo Quick-Fill');
        } finally {
            app()->instance('env', $originalEnv);
        }

        $this->get(route('leads.capture.show'))
            ->assertOk()
            ->assertSee('Talk to our sales team')
            ->assertDontSee('&amp;amp;', false);

        Notification::fake();

        $user = User::query()->where('email', 'superadmin@salescrm.test')->firstOrFail();
        $user->forceFill(['two_factor_enabled' => true])->save();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->get(route('two-factor.challenge'))
            ->assertOk()
            ->assertSee('Verify your sign-in')
            ->assertDontSee('&amp;amp;', false);

        $sentQuotation = Quotation::query()
            ->where('status', QuotationStatus::Sent->value)
            ->firstOrFail();

        $publicUrl = URL::temporarySignedRoute(
            'quotations.public.show',
            now()->addDays(7),
            ['quotation' => $sentQuotation->ulid]
        );

        $this->get($publicUrl)
            ->assertOk()
            ->assertSee($sentQuotation->quote_number)
            ->assertDontSee('&amp;amp;', false);
    }

    public function test_all_authenticated_crm_pages_render_cleanly_with_bell_icon_and_no_lazy_loading_errors(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@salescrm.test')->firstOrFail();
        $rep = User::query()->where('email', 'rep@salescrm.test')->firstOrFail();

        $lead = Lead::query()->firstOrFail();
        $company = Company::query()->firstOrFail();
        $contact = Contact::query()->firstOrFail();
        $pipeline = Pipeline::query()->firstOrFail();
        $deal = Deal::query()->firstOrFail();
        $sentQuotation = Quotation::query()->firstOrFail();

        $draftQuotation = Quotation::query()->create([
            'ulid' => (string) Str::ulid(),
            'team_id' => $deal->team_id,
            'owner_id' => $rep->id,
            'deal_id' => $deal->id,
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'quote_number' => sprintf('QT-%s-99999', now()->format('Y')),
            'version' => 1,
            'status' => QuotationStatus::Draft,
            'currency' => 'BDT',
            'subtotal' => 1000.00,
            'discount_total' => 0.00,
            'tax_total' => 0.00,
            'grand_total' => 1000.00,
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
        ]);

        // Test Rep view scoping on primary workspace pages as well
        foreach ([
            route('dashboard'),
            route('leads.index'),
            route('pipelines.kanban'),
            route('deals.index'),
        ] as $repUrl) {
            $this->actingAs($rep)
                ->get($repUrl)
                ->assertOk()
                ->assertSee('aria-label="Notifications', false)
                ->assertDontSee('>Alerts<', false)
                ->assertDontSee('&amp;amp;', false);
        }

        $appRoutes = [
            route('dashboard'),
            route('leads.index'),
            route('leads.show', $lead),
            route('companies.index'),
            route('companies.show', $company),
            route('contacts.index'),
            route('contacts.show', $contact),
            route('pipelines.index'),
            route('pipelines.kanban'),
            route('pipelines.kanban.show', $pipeline),
            route('deals.index'),
            route('deals.show', $deal),
            route('tasks.index'),
            route('follow-ups.index'),
            route('quotations.index'),
            route('quotations.create'),
            route('quotations.show', $sentQuotation),
            route('quotations.show', $draftQuotation),
            route('reports.index'),
            route('notifications.index'),
            route('imports.index'),
            route('settings.users.index'),
            route('settings.webhooks.index'),
            route('settings.audit-logs.index'),
            route('settings.security.edit'),
        ];

        foreach ($appRoutes as $url) {
            $response = $this->actingAs($superAdmin)->get($url);

            $response->assertOk()
                ->assertSee('Sale Fusion CRM')
                ->assertSee('aria-label="Notifications', false)
                ->assertSee('<svg', false)
                ->assertSee('favicon.svg', false)
                ->assertDontSee('>Alerts<', false)
                ->assertDontSee('&amp;amp;', false);
        }

        // Also check printable quotation page
        $this->actingAs($superAdmin)
            ->get(route('quotations.print', $sentQuotation))
            ->assertOk()
            ->assertSee($sentQuotation->quote_number)
            ->assertSee('favicon.svg', false)
            ->assertDontSee('&amp;amp;', false);

        // Verify password remains hashed as expected for demo logins
        $this->assertTrue(Hash::check('password', $superAdmin->password));
    }
}
