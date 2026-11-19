<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\Webhooks\WebhookDispatcherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

final class DeliverWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly int $webhookId,
        public readonly string $eventName,
        public readonly array $payload,
    ) {}

    public function handle(WebhookDispatcherService $dispatcher): void
    {
        $webhook = Webhook::query()->where('is_active', true)->find($this->webhookId);

        if ($webhook === null) {
            return;
        }

        $scheme = strtolower((string) parse_url($webhook->url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return;
        }

        $envelope = [
            'event' => $this->eventName,
            'timestamp' => now()->toIso8601String(),
            'data' => $this->payload,
        ];

        $json = json_encode($envelope, JSON_THROW_ON_ERROR);
        $signature = $dispatcher->signPayload($json, (string) $webhook->secret);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'SaleFusionCRM-Webhook/1.0',
                    'X-CRM-Event' => $this->eventName,
                    'X-CRM-Signature' => $signature,
                ])
                ->withBody($json, 'application/json')
                ->post($webhook->url);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => $response->status(),
                'failure_count' => $response->successful() ? 0 : $webhook->failure_count + 1,
            ]);
        } catch (Throwable) {
            $webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => 0,
                'failure_count' => $webhook->failure_count + 1,
            ]);
        }
    }
}
