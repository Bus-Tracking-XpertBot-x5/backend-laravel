<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Filament\Resources\OrganizationResource\RelationManagers;
use App\Models\Organization;
use App\Models\User;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Schema;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('Enter user name')->columnSpan(1),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(255)
                            ->required()
                            ->placeholder('Enter Description')
                            ->columnSpan(1),
                        Forms\Components\Select::make('manager_id')
                            ->options(
                                function ($record) {
                                    if ($record) {
                                        return [
                                            $record->manager->id => $record->manager->name,
                                            ...User::whereDoesntHave('organizationManager')
                                                ->where('role', 'manager')
                                                ->get()
                                                ->pluck('name', 'id')
                                                ->toArray()
                                        ];
                                    } else {
                                        return User::whereDoesntHave('organizationManager')
                                            ->where('role', 'manager')
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                }
                            )
                            ->required()
                            ->label('Manager')
                            ->columnSpan(1),

                    ])->columns(2),
                Forms\Components\Section::make('Location')->schema([
                    Map::make('location')
                        ->hiddenLabel()
                        ->afterStateUpdated(function (Set $set, ?array $state): void {
                            $set('latitude', $state['lat']);
                            $set('longitude', $state['lng']);
                        })
                        ->afterStateHydrated(function ($state, $record, Set $set): void {
                            if ($record != null) {
                                $set('location', [
                                    'lat' => $record->latitude,
                                    'lng' => $record->longitude,
                                ]);
                            }
                        })->columnSpan(2),

                    // Hidden fields to store latitude and longitude
                    Forms\Components\TextInput::make('latitude')
                        ->readOnly()
                        ->required()
                        ->numeric()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('longitude')
                        ->readOnly()
                        ->required()
                        ->numeric()
                        ->columnSpan(1),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('manager.name')
                    ->searchable()
                    ->badge()
                    ->label('Manager Name'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->iconSize('lg')->color('secondary')->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->iconSize('lg')->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
