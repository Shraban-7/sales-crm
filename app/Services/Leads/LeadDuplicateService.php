<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Models\Lead;
use App\Models\Team;
use App\Support\PhoneNumber;

final class LeadDuplicateService
{
    public function find(
        Team $team,
        ?string $email,
        ?string $phone,
        ?int $excludeLeadId = null,
    ): ?Lead {
        $email = $email !== null ? mb_strtolower(trim($email)) : null;
        $email = $email !== '' ? $email : null;
        $comparablePhone = PhoneNumber::comparable($phone);

        if ($email === null && $comparablePhone === null) {
            return null;
        }

        $query = Lead::query()
            ->where('team_id', $team->id)
            ->when($excludeLeadId !== null, fn ($q) => $q->whereKeyNot($excludeLeadId));

        $query->where(function ($sub) use ($email, $comparablePhone): void {
            if ($email !== null) {
                $sub->whereRaw('LOWER(email) = ?', [$email]);
            }

            if ($comparablePhone !== null) {
                $method = $email !== null ? 'orWhereRaw' : 'whereRaw';
                $sub->{$method}(
                    "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                    [$comparablePhone],
                );
            }
        });

        return $query->orderBy('id')->first();
    }

    /**
     * @return list<Lead>
     */
    public function allFor(Lead $lead, int $limit = 5): array
    {
        $duplicates = [];

        $candidates = Lead::query()
            ->where('team_id', $lead->team_id)
            ->whereKeyNot($lead->id)
            ->where(function ($query) use ($lead): void {
                if ($lead->email !== null) {
                    $query->whereRaw('LOWER(email) = ?', [mb_strtolower($lead->email)]);
                }

                $comparable = PhoneNumber::comparable($lead->phone);
                if ($comparable !== null) {
                    $method = $lead->email !== null ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                        [$comparable],
                    );
                }
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($candidates as $candidate) {
            $duplicates[] = $candidate;
        }

        return $duplicates;
    }
}
