<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

final class TeamController extends Controller
{
    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $this->authorize('create', Team::class);

        $data = $request->validated();
        Team::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'leader_id' => $data['leader_id'] ?? null,
            'monthly_revenue_target' => $data['monthly_revenue_target'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Team created.');
    }
}
