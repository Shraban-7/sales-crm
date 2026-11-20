<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Actions\Activities\LogActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\StoreActivityRequest;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $user = $request->user();
        $tab = $request->query('tab', 'my'); // 'my' or 'team'
        $type = $request->query('type');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $activities = Activity::query()
            ->with(['user', 'subject'])
            ->when($tab === 'my', fn ($q) => $q->where('user_id', $user->id))
            ->when($tab === 'team' && $user->team_id, fn ($q) => $q->where('team_id', $user->team_id))
            ->when(is_string($type) && $type !== '', fn ($q) => $q->where('type', $type))
            ->when(is_string($dateFrom) && $dateFrom !== '', fn ($q) => $q->whereDate('occurred_at', '>=', $dateFrom))
            ->when(is_string($dateTo) && $dateTo !== '', fn ($q) => $q->whereDate('occurred_at', '<=', $dateTo))
            ->latest('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return view('pages.activities.index', [
            'activities' => $activities,
            'currentTab' => $tab,
            'currentType' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function store(StoreActivityRequest $request, LogActivityAction $logActivityAction): RedirectResponse
    {
        $this->authorize('create', Activity::class);

        $data = $request->validated();
        $modelClass = Relation::getMorphedModel((string) $data['subject_type']);

        if ($modelClass === null) {
            abort(422, 'Invalid subject type.');
        }

        $subject = $modelClass::query()->findOrFail((int) $data['subject_id']);
        $this->authorize('view', $subject);

        $logActivityAction->handle($subject, $request->user(), $data);

        return back()->with('status', 'Activity logged.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return back()->with('status', 'Activity deleted.');
    }
}
