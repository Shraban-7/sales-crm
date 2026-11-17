<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Actions\Activities\LogActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\StoreActivityRequest;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;

final class ActivityController extends Controller
{
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
