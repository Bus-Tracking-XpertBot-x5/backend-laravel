<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusMovementResource\Pages;
use App\Filament\Resources\BusMovementResource\RelationManagers;
use App\Models\Bus;
use App\Models\BusMovement;
use App\Models\Route;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth; // Ensure Auth facade is imported
class BusMovementResource extends Resource
{
    protected static ?string $model = BusMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        // Select Bus using a relationship (make sure Bus model has a "name" attribute)
                        Select::make('bus_id')
                            ->label('Bus')
                            ->options(
                                Bus::all()
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->required()
                            ->columnSpan(1),

                        Select::make('route_id')
                            ->label('Route')
                            ->options(
                                Route::all()
                                    ->pluck('route_name', 'id')
                                    ->toArray()
                            )
                            ->required()
                            ->columnSpan(1),

                        DateTimePicker::make('estimated_start')
                            ->required()
                            ->displayFormat('d-m-Y H:i')
                            ->format('Y-m-d H:i:s')
                            ->minDate(now()) // Prevents selecting a date before today
                            ->rule('after_or_equal:today') // Validates that the start date is not before today
                            ->label('Estimated Start'),

                        DateTimePicker::make('estimated_end')
                            ->required()
                            ->minDate(fn(callable $get) => $get('estimated_start') ?? now()) // End cannot be before start
                            ->rule('after:estimated_start') // Validates that the end date is after the start date
                            ->label('Estimated End'),

                        DateTimePicker::make('actual_start')
                            ->readOnly(),
                        DateTimePicker::make('actual_end')
                            ->readOnly(),

                        TextInput::make('passenger_count')
                            ->readOnly()
                            ->default(0),

                        Select::make('status')
                            ->options([
                                'scheduled'   => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed'   => 'Completed',
                                'cancelled'   => 'Cancelled',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ])
                    ->columns(2), // 2‑column layout for a modern UI
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bus.name')
                    ->searchable()
                    ->badge()
                    ->label('Bus Name'),
                Tables\Columns\TextColumn::make('route.route_name')
                    ->searchable()
                    ->label('Route Name'),

                Tables\Columns\TextColumn::make('estimated_start')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estimated_end')
                    ->searchable(),
                Tables\Columns\TextColumn::make('passenger_count')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled'   => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed'   => 'Completed',
                        'cancelled'   => 'Cancelled',
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
                    return $query->where('organization_id', Auth::user()->organization_id); 
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
            'index' => Pages\ListBusMovements::route('/'),
            'create' => Pages\CreateBusMovement::route('/create'),
            'edit' => Pages\EditBusMovement::route('/{record}/edit'),
        ];
    }

    



}
