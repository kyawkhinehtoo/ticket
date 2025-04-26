<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Incident;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsAdminOverview extends BaseWidget
{
    protected function getStats(): array
    {

        //Stats for Incident
        $month = idate("m");
        $prevMonth = date("n", strtotime('m .-1 month')); //strtotime(date('Y-m')." -1 month"));
        $prevYear = date("Y", strtotime('m .-1 month')); //strtotime(date('Y-m')." -1 month"));
        $year = date("Y", strtotime('m'));
        $currentMonthToDisplay = date("F", strtotime('m')) . ', ' . date("Y", strtotime('m'));
        $previousMonthIncidentCount = Incident::whereYear('created_at', $prevYear)->whereMonth('created_at', $prevMonth)->count();
        $currentIncidentCount = Incident::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

        //
        $currentDate = Carbon::now();
        $startDate = $currentDate->subMonths(12)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $descriptionIcon = 'heroicon-m-arrow-trending-up';
        $descriptionText = abs($previousMonthIncidentCount - $currentIncidentCount) . ' Increase';
        if ($currentIncidentCount < $previousMonthIncidentCount) {
            $descriptionIcon = 'heroicon-m-arrow-trending-down';
            $descriptionText = abs($previousMonthIncidentCount - $currentIncidentCount) . ' Decrease';
        }
        $incidentCounts = Incident::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->pluck('count')
            ->toArray();


        //Stats for Number of Contract to be Expired This Month
        $contracts = Contract::whereYear('contract_to', $year)->whereMonth('contract_to', $month)->count();
        $contractCounts = Contract::whereBetween('contract_to', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw('MONTH(contract_to)'))
            ->orderBy(DB::raw('MONTH(contract_to)'), 'asc')
            ->pluck('count')
            ->toArray();
        $companies = Company::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        $companyCounts = Company::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->pluck('count')
            ->toArray();
        //Status for Number Active Companies 
        return [
            Stat::make('Total Incident In ' . $currentMonthToDisplay, $currentIncidentCount)
                ->description($descriptionText)
                ->descriptionIcon($descriptionIcon)
                ->chart($incidentCounts)
                ->color('success'),
            Stat::make('Contract Expire In ' . $currentMonthToDisplay, $contracts)
                ->description('No. of Contract')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($contractCounts)
                ->color('danger'),
            Stat::make('New Clients In ' . $currentMonthToDisplay, $companies)
                ->description('companies')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($companyCounts)
                ->color('success'),
        ];
    }
}
