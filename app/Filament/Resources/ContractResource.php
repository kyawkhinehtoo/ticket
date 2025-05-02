<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 3;
    public static function canAccess(): bool
    {
        return auth()->user()?->role !== 'engineer';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label('Company')
                    ->required()
                    ->options(Company::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\DatePicker::make('contract_from')
                    ->required(),
                Forms\Components\DatePicker::make('contract_to')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'hourly' => 'Hourly Based',
                        'case' => 'Case Based',
                    ])
                    ->required()
                    ->default('admin'),
                Forms\Components\TextInput::make('default')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('extra')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\FileUpload::make('file')
                    ->label('Upload Contract')
                    ->directory('contracts')
                    ->multiple()
                    ->maxFiles(3)
                    ->acceptedFileTypes(['application/pdf'])
                    ->openable()
                    ->maxSize(4096),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract_to')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                ViewColumn::make('status')->view('tables.columns.is-expire'),
                Tables\Columns\TextColumn::make('default')
                    ->numeric()
                    ->sortable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
