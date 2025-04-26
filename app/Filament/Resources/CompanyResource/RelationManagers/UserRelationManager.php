<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\Company;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(fn ($livewire) => ($livewire instanceof CreateRecord))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => ($livewire instanceof CreateRecord))
                            ->minLength(8)
                            ->same('passwordConfirmation')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label('Password Confirmation')
                            ->password()
                            ->required(fn ($livewire) => ($livewire instanceof CreateRecord))
                            ->minLength(8)
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'engineer' => 'Engineer',
                                'company' => 'Company',
                            ])
                            ->required()
                            ->default('company'),
                    ])->columns(2),
                Section::make('Other Information')
                    ->description('If User Role is Not Company, leave blank to Company field !')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Company')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Toggle::make('disabled')->inline(false)
                            ->default(0),
                        Forms\Components\TextInput::make('last_login_ip')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('last_login_time')
                            ->disabled(),
                    ])->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),



                Tables\Columns\IconColumn::make('disabled')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        '1' => 'heroicon-o-x-circle',
                        '0' => 'heroicon-o-check-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'warning',
                        '0' => 'success',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('last_login_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
