<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverOrganizationResource\Pages;
use App\Models\Driver;
use App\Models\DriverOrganization;
use App\Models\Organization;
use Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DriverOrganizationResource extends Resource
{
    protected static ?string $model = DriverOrganization::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->options(
                                function () {
                                    if (Auth::user()->role === 'manager') {
                                        return Driver::whereDoesntHave('organizations', function ($query) {
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
                            ->searchable(),
                        Forms\Components\Select::make('organization_id')
                            ->label('Organization')
                            ->options(
                                function () {
                                    if (Auth::user()->role === 'manager') {
                                        return Organization::where('id', Auth::user()->organization_id)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                    return Organization::all()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                            )
                            ->default(fn() => Auth::user()->role === 'manager' ? Auth::user()->organization_id : null)
                            ->disabled(fn() => Auth::user()->role === 'manager')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('driver.user.name')
                    ->label('Driver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organization')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDriverOrganizations::route('/'),
            'create' => Pages\CreateDriverOrganization::route('/create'),
            'edit' => Pages\EditDriverOrganization::route('/{record}/edit'),
        ];
    }
} 