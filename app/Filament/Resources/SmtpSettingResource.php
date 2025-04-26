<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmtpSettingResource\Pages\ListSmtpSettings;
use App\Filament\Resources\SmtpSettingResource\Pages\CreateSmtpSetting;
use App\Filament\Resources\SmtpSettingResource\Pages\EditSmtpSetting;
use App\Models\SmtpSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmtpSettingResource extends Resource
{
    protected static ?string $model = SmtpSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider')
                    ->options([
                        'custom' => 'Custom',
                        'gmail' => 'Gmail',
                        'mailgun' => 'Mailgun',
                        'sendgrid' => 'SendGrid',
                        'ses' => 'Amazon SES',
                        'postmark' => 'Postmark',
                    ])
                    ->required()
                    ->live(),
                
                Forms\Components\TextInput::make('host')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('port')
                    ->required()
                    ->numeric(),
                
                Forms\Components\Select::make('encryption')
                    ->options([
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                        '' => 'None',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(),
                
                Forms\Components\TextInput::make('from_address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('from_name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Active Configuration')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('host')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSmtpSettings::route('/'),
            'create' => CreateSmtpSetting::route('/create'),
            'edit' => EditSmtpSetting::route('/{record}/edit'),
        ];
    }
}