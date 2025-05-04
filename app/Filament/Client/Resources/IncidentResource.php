<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\IncidentResource\Pages;
use App\Filament\Client\Resources\IncidentResource\RelationManagers;
use App\Models\Incident;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\Company;
use Filament\Forms\Components\Component;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Split;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Split as ISplit;
use Filament\Infolists\Components\Section as ISection;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Boolean;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([


            Section::make('Open New Ticket')
                ->description('Prevent abuse by limiting the number of requests per period')
                ->schema([
                    Forms\Components\TextInput::make('pic_name')
                        ->label('Name')
                        ->placeholder('Enter Your Name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Radio::make('topic')
                        ->label('Topic')
                        ->options([
                            'PC' => 'PC',
                            'Server' => 'Server', 
                            'Network' => 'Network',
                            'Other' => 'Other',
                        ])
                        ->default('PC')
                        ->inline()
                        ->inlineLabel(false)
                        ->required(),
                    
                    Forms\Components\Textarea::make('description')
                        ->placeholder('Enter your issue details')
                        ->columnSpanFull()
                        ->required(),

                ])
                ->columns(2)
                ->grow(false),




        ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ticket Open At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('topic')->searchable(),
                Tables\Columns\TextColumn::make('pic_name')->label('PIC')->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_time')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('close_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('close_time')
                    ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('is_adhoc')
                    ->label('Request Type')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Adhoc' : 'Contract')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'Open' => 'heroicon-o-eye-slash',
                        'Escalated' => 'heroicon-o-eye',
                        'WIP' => 'heroicon-o-clock',
                        'Close' => 'heroicon-o-document-check',
                    })->color(fn (string $state): string => match ($state) {
                        'Open' => 'warning',
                        'Escalated' => 'danger',
                        'WIP' => 'warning',
                        'Close' => 'info',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('assigned_id')
                    ->label("Engineer")
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(function ($state) {
                        // Assuming $value is an array of tag names
                        $user = User::where('id', $state)->first();
                        return $user?$user->name:null;
                    })
                    ->searchable(),


                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //   Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ISection::make('Open New Ticket')
                    ->schema([
                        TextEntry::make('pic_name')
                            ->label('Created By'),
                        TextEntry::make('topic')
                            ->label('Topic'),
                        TextEntry::make('is_adhoc')
                            ->label('Request Type')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Adhoc' : 'Contract')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'warning' : 'success'),
                        TextEntry::make('description'),
                    ])
                    ->columnSpan(3)
                    ->columns(2)
                    ->grow(false),
                ISection::make('Ticket Details')
                    ->schema([
                        TextEntry::make('status')->label('Ticket Status')
                            ->icon(fn (string $state): string => match ($state) {
                                'Open' => 'heroicon-o-eye-slash',
                                'Escalated' => 'heroicon-o-eye',
                                'WIP' => 'heroicon-o-clock',
                                'Close' => 'heroicon-o-document-check',
                            })->color(fn (string $state): string => match ($state) {
                                'Open' => 'warning',
                                'Escalated' => 'danger',
                                'WIP' => 'warning',
                                'Close' => 'info',
                            }),
                        ISplit::make([
                            TextEntry::make('created_at')->label('Created At')->date(),
                            TextEntry::make('updated_at')->label('Updated At')->date(),
                        ]),
                        ISplit::make([
                            TextEntry::make('start_date')->label('Start Date')->date()
                                ->visible(static fn (Model $model) => ($model->start_date) ? true : false),
                            TextEntry::make('start_time')->label('Start Time')->date()
                                ->visible(static fn (Model $model) => ($model->start_time) ? true : false),
                        ]),
                        TextEntry::make('assigned_id')
                            ->label("Assigned Engineer")
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(function ($state) {
                                // Assuming $value is an array of tag names
                                $user = User::where('id', $state)->first();
                                return $user?$user->name:null;
                            })
                            ->visible(static fn (Model $model) => ($model->assigned_id) ? true : false),


                        IconEntry::make('service_report')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->label('Download Service Report')
                            ->url(static fn (Model $model): string => '/storage/' . $model->service_report, shouldOpenInNewTab: true)
                            ->visible(static fn (Model $model) => ($model->service_report) ? true : false)
                    ])
                    ->columnSpan(1)
                    ->columns(1)
                    ->grow(true),
            ])->columns(4);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // $user = Auth::user();
        // if ($user->isAdmin()) {
        //     return response(403, "Unauthorized Access");
        // }
        return [
            'index' => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),
            'view' => Pages\ViewIncident::route('/{record}'),
            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
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

        // $company = Company::join('users', 'users.company_id', 'company.id')
        //     ->where('users.id', auth()->id())
        //     ->first();

        return parent::getEloquentQuery()->whereIn('company_id',$companyIds)->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}