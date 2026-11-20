<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class StagesController extends Controller
{
    public function index(): View
    {
        $pipelines = Pipeline::query()
            ->with(['stages' => fn ($q) => $q->orderBy('position')])
            ->get();

        return view('pages.settings.stages', [
            'pipelines' => $pipelines,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pipeline_id' => ['required', 'integer', 'exists:pipelines,id'],
            'name' => ['required', 'string', 'max:80'],
            'color' => ['required', 'string', 'max:30'],
            'probability' => ['required', 'integer', 'between:0,100'],
            'rotting_days' => ['nullable', 'integer', 'min:1'],
            'is_won' => ['boolean'],
            'is_lost' => ['boolean'],
        ]);

        $maxPosition = (int) PipelineStage::query()->where('pipeline_id', $data['pipeline_id'])->max('position');

        PipelineStage::query()->create([
            'pipeline_id' => $data['pipeline_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'color' => $data['color'],
            'probability' => $data['probability'],
            'rotting_days' => $data['rotting_days'] ?? null,
            'is_won' => $request->boolean('is_won'),
            'is_lost' => $request->boolean('is_lost'),
            'position' => $maxPosition + 1,
        ]);

        return back()->with('status', 'Pipeline stage added successfully.');
    }
}
