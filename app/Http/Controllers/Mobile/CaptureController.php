<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CaptureController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('pages.mobile.capture', [
            'events' => Event::query()->visibleTo($user)->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:191'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'card_photo' => ['nullable', 'image', 'max:10240'],
        ]);

        $user = $request->user();
        $cardPhotoPath = null;

        if (! empty($data['event_id'])) {
            $event = Event::query()->visibleTo($user)->find((int) $data['event_id']);
            if ($event === null) {
                abort(403, 'Unauthorized event selection: event does not belong to your team.');
            }
        }

        if ($request->hasFile('card_photo')) {
            $file = $request->file('card_photo');
            $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $cardPhotoPath = $file->storeAs('cards/'.$user->team_id, $storedName, 'private');
        }

        $lead = Lead::query()->create([
            'team_id' => $user->team_id,
            'owner_id' => $user->id,
            'event_id' => $data['event_id'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'card_photo_path' => $cardPhotoPath,
            'source' => LeadSource::TradeShow->value,
            'status' => LeadStatus::New,
            'priority' => $data['priority'] ?? 'medium',
            'score' => 20,
        ]);

        if (! empty($data['notes'])) {
            $lead->activities()->create([
                'team_id' => $user->team_id,
                'user_id' => $user->id,
                'type' => 'note',
                'summary' => 'Event Card Capture Note',
                'body' => (string) $data['notes'],
                'occurred_at' => now(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
                'message' => 'Event lead captured successfully.',
            ], 201);
        }

        return back()->with('status', 'Event lead captured successfully.');
    }
}
