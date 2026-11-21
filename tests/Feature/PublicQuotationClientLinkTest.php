<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Team;
use App\Models\User;
use App\Notifications\CrmAlertNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PublicQuotationClientLinkTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    private User $rep;

    private Company $company;

    private Contact $contact;

    private Deal $deal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->team = Team::query()->create(['name' => 'Enterprise Sales', 'slug' => 'enterprise-sales']);
        $this->rep = User::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Nadia Karim',
            'email' => 'nadia@salescrm.test',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $this->rep->assignRole('Sales Rep');

        $this->company = Company::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'name' => 'Bengal Tech Holdings',
        ]);

        $this->contact = Contact::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'company_id' => $this->company->id,
            'first_name' => 'Arif',
            'last_name' => 'Mahmud',
            'email' => 'arif@bengaltech.test',
        ]);

        $pipeline = Pipeline::query()->create(['name' => 'B2B Enterprise', 'is_active' => true]);
        $stage = PipelineStage::query()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Proposal Sent',
            'position' => 1,
            'probability' => 75,
        ]);

        $this->deal = Deal::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'company_id' => $this->company->id,
            'primary_contact_id' => $this->contact->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'title' => 'Bengal Tech Enterprise CRM Rollout',
            'value' => 650000.00,
            'currency' => 'BDT',
            'status' => 'open',
        ]);
    }

    public function test_quotation_automatically_receives_cryptographic_ulid_of_26_characters(): void
    {
        $quotation = Quotation::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'deal_id' => $this->deal->id,
            'company_id' => $this->company->id,
            'contact_id' => $this->contact->id,
            'quote_number' => 'QT-2026-00010',
            'version' => 1,
            'status' => QuotationStatus::Sent,
            'currency' => 'BDT',
            'subtotal' => 650000.00,
            'grand_total' => 650000.00,
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
        ]);

        $this->assertNotEmpty($quotation->ulid);
        $this->assertSame(26, strlen($quotation->ulid));
        $this->assertTrue(Str::isUlid($quotation->ulid));
    }

    public function test_unsigned_access_to_public_quotation_link_returns_403_forbidden(): void
    {
        $quotation = $this->createQuotationFixture(QuotationStatus::Sent);

        // Access directly without cryptographic signature
        $unsignedUrl = "/q/{$quotation->ulid}";
        $this->get($unsignedUrl)->assertForbidden();
    }

    public function test_tampered_signature_returns_403_forbidden(): void
    {
        $quotation = $this->createQuotationFixture(QuotationStatus::Sent);
        $otherQuote = $this->createQuotationFixture(QuotationStatus::Sent);
        $validSignedUrl = $quotation->getPublicSignedUrl(30);

        // Tamper with signature
        $tamperedUrl = $validSignedUrl.'tampered';
        $this->get($tamperedUrl)->assertForbidden();

        // Tamper with ULID in path to access another quotation with the old signature
        $tamperedPathUrl = str_replace($quotation->ulid, $otherQuote->ulid, $validSignedUrl);
        $this->get($tamperedPathUrl)->assertForbidden();
    }

    public function test_expired_signed_url_returns_403_forbidden(): void
    {
        $quotation = $this->createQuotationFixture(QuotationStatus::Sent);

        // Generate URL already expired in the past
        $expiredUrl = URL::temporarySignedRoute(
            'quotations.public.show',
            now()->subMinute(),
            ['quotation' => $quotation->ulid]
        );

        $this->get($expiredUrl)->assertForbidden();
    }

    public function test_draft_quotation_returns_404_not_found_even_with_valid_signature(): void
    {
        $quotation = $this->createQuotationFixture(QuotationStatus::Draft);
        $validSignedUrl = $quotation->getPublicSignedUrl(30);

        $this->get($validSignedUrl)->assertNotFound();
    }

    public function test_valid_signed_url_renders_quotation_details_and_action_urls(): void
    {
        $quotation = $this->createQuotationFixture(QuotationStatus::Sent);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'position' => 1,
            'item_name' => 'Enterprise License (50 Seats)',
            'quantity' => 1,
            'unit_price' => 500000.00,
            'line_total' => 500000.00,
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'position' => 2,
            'item_name' => 'Onboarding Package',
            'quantity' => 1,
            'unit_price' => 150000.00,
            'line_total' => 150000.00,
        ]);

        $validSignedUrl = $quotation->getPublicSignedUrl(30);

        $response = $this->get($validSignedUrl);
        $response->assertOk();
        $response->assertSee($quotation->quote_number);
        $response->assertSee('Bengal Tech Holdings');
        $response->assertSee('Enterprise License (50 Seats)');
        $response->assertSee('Onboarding Package');
        $response->assertSee('650,000.00');
    }

    public function test_client_can_accept_quotation_with_valid_signature_and_notifies_owner(): void
    {
        Notification::fake();

        $quotation = $this->createQuotationFixture(QuotationStatus::Sent);

        $acceptUrl = URL::temporarySignedRoute(
            'quotations.public.accept',
            now()->addDays(30),
            ['quotation' => $quotation->ulid]
        );

        $response = $this->post($acceptUrl, [
            'acceptance_name' => 'Arif Mahmud',
            'acceptance_email' => 'arif@bengaltech.test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Accepted, $quotation->status);
        $this->assertNotNull($quotation->accepted_at);

        Notification::assertSentTo(
            $this->rep,
            CrmAlertNotification::class,
            function (CrmAlertNotification $notification): bool {
                return str_contains($notification->title, 'Quotation accepted');
            }
        );
    }

    public function test_client_can_reject_quotation_with_valid_signature_and_records_reason(): void
    {
        Notification::fake();

        $quotation = $this->createQuotationFixture(QuotationStatus::Sent);

        $rejectUrl = URL::temporarySignedRoute(
            'quotations.public.reject',
            now()->addDays(30),
            ['quotation' => $quotation->ulid]
        );

        $response = $this->post($rejectUrl, [
            'rejection_reason' => 'Postponed until next quarter budget approval.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Rejected, $quotation->status);
        $this->assertNotNull($quotation->rejected_at);
        $this->assertSame('Postponed until next quarter budget approval.', $quotation->rejection_reason);

        Notification::assertSentTo(
            $this->rep,
            CrmAlertNotification::class,
            function (CrmAlertNotification $notification): bool {
                return str_contains($notification->title, 'Quotation declined');
            }
        );
    }

    private function createQuotationFixture(QuotationStatus $status): Quotation
    {
        return Quotation::query()->create([
            'team_id' => $this->team->id,
            'owner_id' => $this->rep->id,
            'deal_id' => $this->deal->id,
            'company_id' => $this->company->id,
            'contact_id' => $this->contact->id,
            'quote_number' => 'QT-'.strtoupper(Str::random(8)),
            'version' => 1,
            'status' => $status,
            'currency' => 'BDT',
            'subtotal' => 650000.00,
            'grand_total' => 650000.00,
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
        ]);
    }
}
