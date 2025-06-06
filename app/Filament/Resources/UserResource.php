<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // User Information Section
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('Enter user name')->columnSpan(1),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->placeholder('Enter email address')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->visibleOn('create')
                            ->required()
                            ->minLength(8)
                            ->maxLength(191)
                            ->placeholder('Enter password')
                            ->helperText('Password must be at least 8 characters')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('phone_number')
                            ->placeholder('Enter Phone Number')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        Forms\Components\Select::make('role')
                            ->options(function () {
                                $user = Auth::user();
                                // If the user is a manager, restrict the options
                                if ($user && $user->role === 'manager') {
                                    return [
                                        'driver' => 'Driver',
                                    ];
                                }

                                // Otherwise, allow all roles
                                return [
                                    'admin' => 'Admin',
                                    'manager' => 'Manager',
                                    'driver' => 'Driver',
                                    'passenger' => 'Passenger',
                                ];
                            })
                            ->required()
                            ->default(function () {
                                $user = Auth::user();
                                return ($user && $user->role === 'manager') ? 'driver' : 'manager';
                            })
                            ->label('User Role')
                            ->columnSpan(1),

                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Full Name'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email Address'),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->label('Phone Number'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->searchable()
                    ->label('Role'),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'driver' => 'Driver',
                    ])
                    ->label('Role')
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconSize('lg')->color('secondary')->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->iconSize('lg')->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->role === 'manager') {
                    return $query->where(['organization_id' => Auth::user()->organization_id, 'role' => 'passenger']);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
