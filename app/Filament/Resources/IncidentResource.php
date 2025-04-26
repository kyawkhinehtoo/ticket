<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentResource\Pages;
use App\Filament\Resources\IncidentResource\RelationManagers;
use App\Models\Company;
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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Incident Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->required(),
                        Forms\Components\TimePicker::make('start_time')
                            ->required(),
                        Forms\Components\DatePicker::make('close_date')
                            ->afterOrEqual('start_date')
                            ->requiredIf('status',  'Close'),
                        Forms\Components\TimePicker::make('close_time')
                            ->requiredIf('status',  'Close'),
                    ])
                    ->columns(2),

                Section::make()
                    ->schema([
                        Forms\Components\Radio::make('topic')
                            ->label('Topic')
                            ->options([
                                'PC' => 'PC',
                                'Server' => 'Server',
                                'Network' => 'Network',
                                'Other' => 'Other',
                            ])
                            ->inline()
                            ->inlineLabel(false)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->required(),
                        Forms\Components\TextInput::make('pic_name')
                            ->label('Company PIC Name')
                            ->maxLength(255),
                        Forms\Components\Select::make('company_id')
                            ->label('Company')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable(),

                    ])
                    ->columns(2),
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'Onsite' => 'Onsite Support (Min 2 Hrs)',
                                'Remote' => 'Remote Support (Min 1 Hrs)',
                            ])
                            ->required()
                            ->default('admin'),
                        Forms\Components\Radio::make('status')
                            ->label('Topic')
                            ->options([
                                'Open' => 'Open',
                                'Escalated' => 'Escalated',
                                'WIP' => 'WIP',
                                'Close' => 'Close',
                            ])
                            ->inline()
                            ->inlineLabel(false)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('assigned_id')
                            ->label('Assigned To')
                            ->searchable()
                            ->multiple()
                            ->options(User::where('role', 'engineer')->pluck('name', 'id'))
                            ->requiredUnless('status',  'Open'),

                        Forms\Components\FileUpload::make('service_report')
                            ->label('Upload Service Report')
                            ->required(fn (Get $get) => $get('status') === 'Close')
                            ->validationMessages([
                                'required' => 'Please upload "Service Report Form" in order to close the ticket !'
                            ])
                            ->directory('service_report')
                            ->acceptedFileTypes(['application/pdf'])
                            ->openable()
                            ->maxSize(4096),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Open At')
                    ->dateTime()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('topic'),
                Tables\Columns\TextColumn::make('assigned_id')
                    ->label("Engineer")
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(function ($state) {
                        // Assuming $value is an array of tag names

                        $user = User::where('id', $state)->first();
                        return $user->name;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('pic_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Open' => 'Open',
                        'Escalated' => 'Escalated',
                        'WIP' => 'WIP',
                        'Close' => 'Close',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name'),

                Tables\Filters\TrashedFilter::make(),

            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),
            // 'view' => Pages\ViewIncident::route('/{record}'),
            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'open')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'open')->count() > 10 ? 'warning' : 'primary';
    }
}
