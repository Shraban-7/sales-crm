<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\DemoCrmSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(DemoCrmSeeder::class);
    }

    public function test_missing_route_renders_branded_404_page_for_guests_and_users(): void
    {
        $this->get('/nonexistent-crm-page-404')
            ->assertNotFound()
            ->assertSee('HTTP 404')
            ->assertSee('Page or record not found')
            ->assertSee('Go to Sign In')
            ->assertSee('favicon.svg', false)
            ->assertDontSee('&amp;amp;', false);

        $rep = User::query()->where('email', 'rep@salescrm.test')->firstOrFail();

        $this->actingAs($rep)
            ->get('/nonexistent-crm-page-404')
            ->assertNotFound()
            ->assertSee('HTTP 404')
            ->assertSee('Back to Dashboard')
            ->assertDontSee('&amp;amp;', false);
    }

    public function test_unauthorized_request_and_invalid_signature_render_branded_403_page(): void
    {
        $analyst = User::query()->where('email', 'analyst@salescrm.test')->firstOrFail();

        $this->actingAs($analyst)
            ->get(route('settings.users.index'))
            ->assertForbidden()
            ->assertSee('HTTP 403')
            ->assertSee('Access restricted')
            ->assertSee('Back to Dashboard')
            ->assertDontSee('&amp;amp;', false);

        $quote = Quotation::query()->firstOrFail();

        $this->get(route('quotations.public.show', $quote))
            ->assertForbidden()
            ->assertSee('HTTP 403')
            ->assertSee('Invalid signature.')
            ->assertDontSee('&amp;amp;', false);
    }

    public function test_all_error_view_templates_render_cleanly_without_exposing_internal_details(): void
    {
        $cases = [
            '401' => 'Authentication required',
            '402' => 'Action requires active access',
            '403' => 'Access restricted',
            '404' => 'Page or record not found',
            '419' => 'Page session expired',
            '429' => 'Too many requests',
            '500' => 'Unexpected server error',
            '503' => 'Scheduled maintenance in progress',
        ];

        $internalException = new HttpException(500, 'SQLSTATE[42S02]: sensitive internal detail');

        foreach ($cases as $code => $expectedTitle) {
            $html = view("errors.{$code}", [
                'exception' => $code === '500' ? $internalException : new HttpException((int) $code),
            ])->render();

            $this->assertStringContainsString("HTTP {$code}", $html);
            $this->assertStringContainsString($expectedTitle, $html);
            $this->assertStringContainsString('favicon.svg', $html);
            $this->assertStringNotContainsString('&amp;amp;', $html);
            $this->assertStringNotContainsString('SQLSTATE[42S02]', $html);
        }
    }
}
