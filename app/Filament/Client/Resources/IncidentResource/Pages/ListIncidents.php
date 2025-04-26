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
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        $tabs = ['Open' => 'Open', 'Escalated' => 'Escalated', 'WIP' => 'WIP', 'Close' => 'Close', 'All' => 'All',];

        $company = Company::join('users', 'users.company_id', 'company.id')
            ->where('users.id', auth()->id())
            ->first();


        $tabs['Open'] = Tab::make('Open')
            ->icon('heroicon-m-ticket')
            ->badge(Incident::where('status', 'open')->where('company_id', $company->id)->count())
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', 'open');
            });
        $tabs['Escalated'] = Tab::make('Escalated')
            ->icon('heroicon-m-clock')
            ->badge(Incident::where('status', 'escalated')->count())
            ->modifyQueryUsing(function ($query) use ($company) {
                return $query->where('status', 'Escalated')->where('company_id', $company->id);
            });
        $tabs['WIP'] = Tab::make('WIP')
            ->icon('heroicon-m-check-circle')
            ->badge(Incident::where('status', 'wip')->where('company_id', $company->id)->count())
            ->modifyQueryUsing(function ($query) use ($company) {
                return $query->where('status', 'WIP')->where('company_id', $company->id);
            });
        $tabs['Close'] = Tab::make('Close')
            ->icon('heroicon-m-archive-box-arrow-down')
            ->badge(Incident::where('status', 'close')->where('company_id', $company->id)->count())
            ->modifyQueryUsing(function ($query) use ($company) {
                return $query->where('status', 'Close')->where('company_id', $company->id);
            });
        $tabs['All'] = Tab::make('All')
            ->icon('heroicon-m-circle-stack')
            ->badge(Incident::whereNull('deleted_at')->where('company_id', $company->id)->count())
            ->modifyQueryUsing(function ($query) use ($company) {
                return $query->whereNotNull('status')->where('company_id', $company->id);
            });
        return $tabs;
    }
}
