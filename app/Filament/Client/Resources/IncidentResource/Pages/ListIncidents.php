<?php

namespace App\Filament\Client\Resources\IncidentResource\Pages;

use App\Filament\Client\Resources\IncidentResource;
use App\Models\Company;
use App\Models\Incident;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Support\Facades\DB;

class ListIncidents extends ListRecords
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->user()->role !== 'maincon') {
            return [
                Actions\CreateAction::make()->label('Add Incident'),
            ];
        }
         return [];
    }
    public function getTabs(): array
    {
        $tabs = ['Open' => 'Open', 'Escalated' => 'Escalated', 'WIP' => 'WIP', 'Close' => 'Close', 'All' => 'All'];
        $user = auth()->user();

        if ($user->role === 'maincon') {
            // Get all company IDs for this maincon
            $companyIds = \App\Models\Company::where('maincon_id', $user->maincon_id)->pluck('id');
        } else {
            // Default: just the user's company
            $company = \App\Models\Company::join('users', 'users.company_id', 'company.id')
                ->where('users.id', $user->id)
                ->select('company.id')
                ->first();
            $companyIds = [$company->id];
        }

        $tabs['Open'] = Tab::make('Open')
            ->icon('heroicon-m-ticket')
            ->badge(Incident::where('status', 'open')->whereIn('company_id', $companyIds)->count())
            ->modifyQueryUsing(function ($query) use ($companyIds) {
                return $query->where('status', 'open')->whereIn('company_id', $companyIds);
            });
        $tabs['Escalated'] = Tab::make('Escalated')
            ->icon('heroicon-m-clock')
            ->badge(Incident::where('status', 'escalated')->whereIn('company_id', $companyIds)->count())
            ->modifyQueryUsing(function ($query) use ($companyIds) {
                return $query->where('status', 'Escalated')->whereIn('company_id', $companyIds);
            });
        $tabs['WIP'] = Tab::make('WIP')
            ->icon('heroicon-m-check-circle')
            ->badge(Incident::where('status', 'wip')->whereIn('company_id', $companyIds)->count())
            ->modifyQueryUsing(function ($query) use ($companyIds) {
                return $query->where('status', 'WIP')->whereIn('company_id', $companyIds);
            });
        $tabs['Close'] = Tab::make('Close')
            ->icon('heroicon-m-archive-box-arrow-down')
            ->badge(Incident::where('status', 'close')->whereIn('company_id', $companyIds)->count())
            ->modifyQueryUsing(function ($query) use ($companyIds) {
                return $query->where('status', 'Close')->whereIn('company_id', $companyIds);
            });
        $tabs['All'] = Tab::make('All')
            ->icon('heroicon-m-circle-stack')
            ->badge(Incident::whereNull('deleted_at')->whereIn('company_id', $companyIds)->count())
            ->modifyQueryUsing(function ($query) use ($companyIds) {
                return $query->whereNotNull('status')->whereIn('company_id', $companyIds);
            });
        return $tabs;
    }
}
