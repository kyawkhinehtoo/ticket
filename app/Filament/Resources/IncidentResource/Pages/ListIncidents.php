<?php

namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use App\Models\Incident;

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

        $tabs['Open'] = Tab::make('Open')
            ->icon('heroicon-m-ticket')
            ->badge(Incident::where('status', 'open')->count())
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', 'open');
            });
        $tabs['Escalated'] = Tab::make('Escalated')
            ->icon('heroicon-m-clock')
            ->badge(Incident::where('status', 'escalated')->count())
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', 'Escalated');
            });
        $tabs['WIP'] = Tab::make('WIP')
            ->icon('heroicon-m-check-circle')
            ->badge(Incident::where('status', 'wip')->count())
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', 'WIP');
            });
        $tabs['Close'] = Tab::make('Close')
            ->icon('heroicon-m-archive-box-arrow-down')
            ->badge(Incident::where('status', 'close')->count())
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', 'Close');
            });
        $tabs['All'] = Tab::make('All')
            ->icon('heroicon-m-circle-stack')
            ->badge(Incident::whereNull('deleted_at')->count())
            ->modifyQueryUsing(function ($query) {
                return $query->whereNotNull('status');
            });
        return $tabs;
    }
}
