<?php

declare(strict_types=1);

namespace App\Http\Controllers\FollowUps;

use App\Actions\FollowUps\CompleteFollowUpAction;
use App\Enums\FollowUpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUps\CompleteFollowUpRequest;
use App\Http\Requests\FollowUps\StoreFollowUpRequest;
use App\Models\Deal;
use App\Models\FollowUp;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FollowUpController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FollowUp::class);

        $user = $request->user();
        $baseQuery = FollowUp::query()->with(['subject', 'owner'])->visibleTo($user);

        $overdue = (clone $baseQuery)
            ->where('status', FollowUpStatus::Scheduled->value)
            ->where('scheduled_at', '<', now())
            ->orderBy('scheduled_at')
            ->get();

        $today = (clone $baseQuery)
            ->where('status', FollowUpStatus::Scheduled->value)
            ->whereBetween('scheduled_at', [now(), now()->endOfDay()])
            ->orderBy('scheduled_at')
            ->get();

        $upcoming = (clone $baseQuery)
            ->where('status', FollowUpStatus::Scheduled->value)
            ->where('scheduled_at', '>', now()->endOfDay())
            ->orderBy('scheduled_at')
            ->limit(25)
            ->get();

        $deals = Deal::query()->visibleTo($user)->orderBy('title')->get(['id', 'title']);

        return view('pages.follow-ups.index', compact('overdue', 'today', 'upcoming', 'deals'));
    }

    public function store(StoreFollowUpRequest $request): RedirectResponse
    {
        $this->authorize('create', FollowUp::class);

        $data = $request->validated();
        $modelClass = Relation::getMorphedModel((string) $data['subject_type']);
        if ($modelClass === null) {
            abort(422);
        }

        $subject = $modelClass::query()->findOrFail((int) $data['subject_id']);
        $this->authorize('view', $subject);

        $user = $request->user();
        FollowUp::query()->create([
            'team_id' => $user->team_id,
            'owner_id' => $user->id,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'channel' => $data['channel'],
            'status' => FollowUpStatus::Scheduled,
            'scheduled_at' => $data['scheduled_at'],
            'objective' => $data['objective'],
        ]);

        return back()->with('status', 'Follow-up scheduled.');
    }

    public function complete(
        CompleteFollowUpRequest $request,
        FollowUp $followUp,
        CompleteFollowUpAction $completeAction,
    ): RedirectResponse {
        $this->authorize('update', $followUp);

        $nextData = null;
        if ($request->boolean('schedule_next')) {
            $nextData = [
                'channel' => $request->validated('next_channel'),
                'scheduled_at' => $request->validated('next_scheduled_at'),
                'objective' => $request->validated('next_objective'),
            ];
        }

        $completeAction->handle(
            followUp: $followUp,
            actor: $request->user(),
            outcomeNotes: (string) $request->validated('outcome_notes'),
            nextFollowUpData: $nextData,
        );

        return back()->with('status', 'Follow-up completed.');
    }

    public function destroy(FollowUp $followUp): RedirectResponse
    {
        $this->authorize('delete', $followUp);

        $followUp->delete();

        return back()->with('status', 'Follow-up deleted.');
    }
}
