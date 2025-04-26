<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Incident;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ContractHoursWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get companies with active hourly contracts
        $companies = Company::whereHas('contract', function ($query) {
            $query->where('type', 'hourly')
                ->where('contract_to', '>=', now());
        })->with(['contract' => function ($query) {
            $query->where('type', 'hourly')
                ->where('contract_to', '>=', now());
        }])->get();

        $stats = [];

        foreach ($companies as $company) {
            $contract = $company->contract->first();
            if (!$contract) continue;

            // Calculate total hours from incidents with proper conditions
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
                    
                    // Ensure we're calculating positive hours
                    if ($endDateTime->lt($startDateTime)) {
                        return 2; // Minimum hours if dates are incorrect
                    }
                    
                    // Calculate hours and apply minimum of 2 hours
                    return max(2, $endDateTime->diffInHours($startDateTime));
                });
                
        
            $stats[] = Stat::make($company->name, $usedHours . ' / ' . $contract->default . ' hours')
                ->description('Used/Total Hours')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([$usedHours, $contract->default - $usedHours])
                ->color($usedHours >= $contract->default ? 'danger' : 'success');
        }

        return $stats;
    }
}