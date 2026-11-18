<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Enums\LeadStatus;
use App\Models\Team;
use App\Models\User;

final class LeadAssignmentService
{
    public function assignForTeam(Team $team): ?User
    {
        if (! (bool) config('crm.lead_capture.round_robin', true)) {
            return $this->fallbackOwner($team);
        }

        $candidate = User::query()
            ->role('Sales Rep')
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->withCount(['leads as open_leads_count' => function ($query): void {
                $query->whereNotIn('status', [LeadStatus::Converted->value, LeadStatus::Disqualified->value]);
            }])
            ->orderBy('open_leads_count')
            ->orderBy('id')
            ->first();

        return $candidate ?? $this->fallbackOwner($team);
    }

    private function fallbackOwner(Team $team): ?User
    {
        if ($team->leader_id !== null) {
            $leader = User::query()->whereKey($team->leader_id)->where('is_active', true)->first();

            if ($leader !== null) {
                return $leader;
            }
        }

        return User::query()
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
