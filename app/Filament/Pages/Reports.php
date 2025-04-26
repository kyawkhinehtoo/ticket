<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\Incident;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Reports extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.reports';
    
    public ?string $companyId = null;
    public $debugInfo = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
  
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('companyId')
                    ->label('Select Company')
                    ->options(Company::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->resetTable();
                    })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                if (!$this->companyId) {
                    return Incident::query()->whereNull('id'); // Empty query if no company selected
                }
                
                try {
                    // Use a direct query to get the data
                    $results = Incident::query()
                        ->select(
                            'id',
                            'type',
                            DB::raw('SUM(
                                GREATEST(
                                    2,
                                    TIMESTAMPDIFF(
                                        HOUR, 
                                        CONCAT(start_date, " ", start_time), 
                                        CONCAT(close_date, " ", close_time)
                                    )
                                )
                            ) as total_hours')
                        )
                        ->where('company_id', $this->companyId)
                        ->whereNull('deleted_at')
                        ->whereNotNull('close_date')
                        ->whereNotNull('close_time')
                        ->groupBy('type');
                        
                   return $results; 
                   
                    
                } catch (\Exception $e) {
                    $this->debugInfo['error'] = $e->getMessage();
                    return Incident::query()->whereNull('id');
                }
            })
            ->columns([
                TextColumn::make('type')
                    ->label('Incident Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->sortable(),
            ])
            ->emptyStateHeading('No data available')
            ->emptyStateDescription(function() {
                if (!$this->companyId) {
                    return 'Select a company to view incident hours.';
                }
                
                if (!empty($this->debugInfo)) {
                    return 'Debug info: ' . json_encode($this->debugInfo);
                }
                
                return 'No incident hours data found for this company.';
            })
            ->defaultSort('type');
    }
}