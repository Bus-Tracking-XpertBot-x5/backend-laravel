<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusResource\Pages;
use App\Filament\Resources\BusResource\RelationManagers;
use App\Models\Bus;
use App\Models\Driver;
use DB;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(20)
                        ->placeholder('Enter Name')
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('license_plate')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20)
                        ->placeholder('Enter License Plate')
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('capacity')
                        ->required()
                        ->numeric()
                        ->maxLength(60)
                        ->placeholder('Enter Capacity')
                        ->columnSpan(1),
                    Forms\Components\Select::make('status')
                        ->options([
                            'inactive' => 'Inactive',
                            'active' => 'Active',
                            'maintenance' => 'Under Maintenance',
                        ])
                        ->required()
                        ->default('active')
                        ->columnSpan(1),
                    Forms\Components\Select::make('driver_id')
                        ->options(
                            function () {
                                if (Auth::user()->role === 'manager') {
                                    return Driver::whereHas('organizations', function ($query) {
                                        $query->where('organization_id', Auth::user()->organization_id);
                                    })->get()
                                        ->pluck('user.name', 'id')
                                        ->toArray();
                                }
                                return Driver::all()
                                    ->pluck('user.name', 'id')
                                    ->toArray();
                            }
                        )
                        ->required()
                        ->label('Driver')
                        ->columnSpan(1),
                ])->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('organizations')
                            ->label('Assign Organizations')
                            ->relationship('organizations', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->default(fn() => Auth::user()->role === 'manager' ? [Auth::user()->organization_id] : [])
                            ->hidden(fn() => Auth::user()->role === 'manager'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver.user.name')
                    ->label('Driver'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable()
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'inactive' => 'Inactive',
                        'active' => 'Active',
                        'maintenance' => 'Under Maintenance',
                    ])
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

                    // Filter buses that belong to the manager's organization
                    return $query->whereHas('organizations', function ($q) use ($managerOrganizationId) {
                        $q->where('organization_id', $managerOrganizationId);
                    });
                }
            });
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
            'index' => Pages\ListBuses::route('/'),
            'create' => Pages\CreateBus::route('/create'),
            'edit' => Pages\EditBus::route('/{record}/edit'),
        ];
    }
}
