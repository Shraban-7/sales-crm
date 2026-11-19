<?php

declare(strict_types=1);

namespace App\Services\Webhooks;

use App\Enums\WebhookEvent;
use App\Jobs\DeliverWebhookJob;
use App\Models\Webhook;

final class WebhookDispatcherService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(WebhookEvent $event, array $payload, ?int $teamId = null): int
    {
        $webhooks = Webhook::query()
            ->where('is_active', true)
            ->when(
                $teamId !== null,
                fn ($query) => $query->where(function ($sub) use ($teamId): void {
                    $sub->whereNull('team_id')->orWhere('team_id', $teamId);
                }),
                fn ($query) => $query->whereNull('team_id'),
            )
            ->get();

        $dispatched = 0;

        foreach ($webhooks as $webhook) {
            $subscribedEvents = is_array($webhook->events) ? $webhook->events : [];

            if (! in_array($event->value, $subscribedEvents, true)) {
                continue;
            }

            DeliverWebhookJob::dispatch($webhook->id, $event->value, $payload);
            $dispatched++;
        }

        return $dispatched;
    }

    public function signPayload(string $jsonPayload, string $secret): string
    {
        return hash_hmac('sha256', $jsonPayload, $secret);
    }
}
