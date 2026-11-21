<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MobileCardCaptureTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    private User $rep;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->team = Team::query()->create(['name' => 'Field Sales', 'slug' => 'field-sales']);
        $this->rep = User::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Tariq Rahman',
            'email' => 'tariq@salescrm.test',
            'password' => bcrypt('Password@12345'),
            'is_active' => true,
        ]);
        $this->rep->assignRole('Sales Rep');

        $this->event = Event::query()->create([
            'team_id' => $this->team->id,
            'created_by_id' => $this->rep->id,
            'name' => 'Dhaka Tech Expo 2026',
            'location' => 'BICC, Dhaka',
        ]);
    }

    public function test_mobile_capture_screen_renders_with_camera_input_and_pwa_indicators(): void
    {
        $response = $this->actingAs($this->rep)->get(route('mobile.capture'));

        $response->assertOk();
        $response->assertSee('Event &amp; Trade Show Lead Capture', false);
        $response->assertSee('capture="environment"', false);
        $response->assertSee('name="card_photo"', false);
        $response->assertSee('Dhaka Tech Expo 2026');
    }

    public function test_sales_rep_can_capture_lead_with_business_card_photo_stored_on_private_disk(): void
    {
        Storage::fake('private');

        $cardPhoto = UploadedFile::fake()->image('business-card.png', 800, 500);

        $response = $this->actingAs($this->rep)->post(route('mobile.capture.store'), [
            'event_id' => $this->event->id,
            'first_name' => 'Kamrul',
            'last_name' => 'Hasan',
            'company_name' => 'Apex Textiles BD',
            'job_title' => 'IT Director',
            'email' => 'kamrul@apextextiles.test',
            'phone' => '+8801811000301',
            'priority' => 'high',
            'notes' => 'Met at Booth #42. Interested in multi-team enterprise rollout.',
            'card_photo' => $cardPhoto,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Event lead captured successfully.');

        $lead = Lead::query()->where('email', 'kamrul@apextextiles.test')->firstOrFail();

        $this->assertSame($this->team->id, $lead->team_id);
        $this->assertSame($this->rep->id, $lead->owner_id);
        $this->assertSame($this->event->id, $lead->event_id);
        $this->assertSame('Kamrul', $lead->first_name);
        $this->assertSame('Hasan', $lead->last_name);
        $this->assertSame('Apex Textiles BD', $lead->company_name);
        $this->assertSame('IT Director', $lead->job_title);
        $this->assertSame(LeadSource::TradeShow, $lead->source);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame('high', $lead->priority);
        $this->assertSame(20, $lead->score);

        $this->assertNotNull($lead->card_photo_path);
        $this->assertStringStartsWith('cards/'.$this->team->id.'/', $lead->card_photo_path);
        Storage::disk('private')->assertExists($lead->card_photo_path);

        // Verify activity note was created
        $this->assertDatabaseHas('activities', [
            'subject_type' => 'lead',
            'subject_id' => $lead->id,
            'type' => 'note',
            'summary' => 'Event Card Capture Note',
        ]);
    }

    public function test_mobile_capture_supports_json_api_for_offline_sync(): void
    {
        Storage::fake('private');

        $response = $this->actingAs($this->rep)
            ->postJson(route('mobile.capture.store'), [
                'event_id' => $this->event->id,
                'first_name' => 'Nusrat',
                'last_name' => 'Jahan',
                'company_name' => 'Crescent Retail',
                'email' => 'nusrat@crescentretail.test',
                'priority' => 'urgent',
                'notes' => 'Needs urgent proposal by Monday.',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Event lead captured successfully.',
        ]);

        $leadId = $response->json('lead_id');
        $this->assertNotNull($leadId);
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'email' => 'nusrat@crescentretail.test',
            'priority' => 'urgent',
        ]);
    }

    public function test_mobile_capture_requires_first_name(): void
    {
        $response = $this->actingAs($this->rep)->post(route('mobile.capture.store'), [
            'company_name' => 'Nameless Lead Corp',
        ]);

        $response->assertSessionHasErrors(['first_name']);
    }
}
