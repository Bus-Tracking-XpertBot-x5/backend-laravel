<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('license_number')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Enter License Number'),
                        Forms\Components\Select::make('user_id')
                            ->options(
                                function ($record) {
                                    if ($record) {
                                        return [
                                            $record->user->id => $record->user->name,
                                            ...User::whereDoesntHave('driver')
                                                ->where('role', 'driver')
                                                ->get()
                                                ->pluck('name', 'id')
                                                ->toArray()
                                        ];
                                    } else {
                                        return User::whereDoesntHave('driver')
                                            ->where('role', 'driver')
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                }
                            )
                            ->required()
                            ->label('User')
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->searchable()
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconSize('lg')->color('secondary')->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->iconSize('lg')->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->role === 'manager') {
                    // Get the manager's organization ID
                    $managerOrganizationId = Auth::user()->organization_id;

                    // Filter drivers that belong to the manager's organization
                    return $query->whereHas('organizations', function ($q) use ($managerOrganizationId) {
                        $q->where('organization_id', $managerOrganizationId);
                    });
                }
            })
        ;
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
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
