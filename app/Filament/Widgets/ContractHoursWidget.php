<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Incident;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ContractHoursWidget extends Widget
{
    protected static string $view = 'filament.widgets.contract-hours-widget';

    public function getCompanies()
    {
        $user = auth()->user();
        if ($user->role === 'maincon') {
            $companyIds = \App\Models\Company::where('maincon_id', $user->maincon_id)->pluck('id');
        } else if ($user->role === 'company') {
            $companyIds = \App\Models\Company::join('users', 'users.company_id', 'company.id')
                ->where('users.id', $user->id)
                ->pluck('company.id');
        } else if ($user->role === 'admin') {
            $companyIds = \App\Models\Company::pluck('id');
        } else {
            $companyIds = [];
        }

        $companies = Company::whereHas('contract', function ($query) {
                $query->where('type', 'hourly')
                    ->where('contract_to', '>=', now());
            })
            ->with(['contract' => function ($query) {
                $query->where('type', 'hourly')
                    ->where('contract_to', '>=', now());
            }])
            ->whereIn('company.id', $companyIds)
            ->get();

        // Attach used_hours and total_hours to each company
        foreach ($companies as $company) {
            $contract = $company->contract->first();
            if (!$contract) {
                $company->used_hours = 0;
                $company->total_hours = 0;
                continue;
            }
            $usedHours = Incident::where('incident.company_id', $company->id)
                ->join('company', 'incident.company_id', 'company.id')
                ->join('contract', 'company.id', 'contract.company_id')
                ->whereNotNull('close_date')
                ->whereNotNull('close_time')
                ->where('contract.type', 'hourly')
                ->where('contract.contract_to', '>=', now())
                ->whereNull('contract.deleted_at')
                ->whereNull('company.deleted_at')
                ->whereNull('incident.deleted_at')
                ->get()
                ->sum(function ($incident) {
                    $startDateTime = Carbon::parse($incident->start_date . ' ' . $incident->start_time);
                    $endDateTime = Carbon::parse($incident->close_date . ' ' . $incident->close_time);
                    if ($endDateTime->lt($startDateTime)) {
                        return 2;
                    }
                    return max(2, $endDateTime->diffInHours($startDateTime));
                });
            $company->used_hours = $usedHours;
            $company->total_hours = $contract->default;
        }
        return $companies;
    }
}