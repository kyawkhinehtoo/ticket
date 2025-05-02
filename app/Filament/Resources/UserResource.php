<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Company;
use App\Models\MainCon;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section as ISection;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User';

    protected static ?int $navigationSort = 1;
    public static function canAccess(): bool
    {
        return auth()->user()?->role !== 'engineer';
    }
    public static function form(Form $form): Form
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
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => ($livewire instanceof CreateRecord))
                            ->minLength(8)
                            ->revealable()
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
                                'maincon' => 'MainCon',
                            ])
                            ->reactive()
                            ->required()
                            ->default('admin'),
                    ])
                    ->columns(2)
                    ->columnSpan(3),
                Section::make('Other Information')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Company')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable()
                            ->hidden(fn (Get $get): bool => $get('role') != 'company'),
                        Forms\Components\Select::make('maincon_id')
                            ->label('MainCon')
                            ->options(MainCon::all()->pluck('name', 'id'))
                            ->searchable()
                            ->hidden(fn (Get $get): bool => $get('role') != 'maincon'),
                        Forms\Components\Toggle::make('disabled')->inline(false)
                            ->default(0),

                    ])->columnSpan(1)
            ])->columns(4);
    }

    public static function table(Table $table): Table
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
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ISection::make('User Information')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('role'),
                    ])
                    ->columns(2)
                    ->columnSpan(3),
                ISection::make('Other Information')
                    ->schema([
                        TextEntry::make('company.name')
                            ->hidden(fn ($record): bool => $record->role !== 'company')
                            ->label('Company'),
                        TextEntry::make('maincon.name')
                            ->hidden(fn ($record): bool => $record->role !== 'maincon')
                            ->label('MainCon'),
                        IconEntry::make('disabled')
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

                    ])->columnSpan(1)
            ])->columns(4);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
