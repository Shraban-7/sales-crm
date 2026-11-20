<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $events = Event::query()
            ->withCount('leads')
            ->visibleTo($user)
            ->latest()
            ->paginate(15);

        return view('pages.mobile.events', [
            'events' => $events,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:191'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        Event::query()->create([
            'team_id' => $user->team_id,
            'created_by_id' => $user->id,
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('status', 'Event created successfully.');
    }
}
