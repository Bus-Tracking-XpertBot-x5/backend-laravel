<?php

namespace App\Filament\Widgets;

use App\Models\BusMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use Auth;
use Carbon\Carbon;
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $new_passengers=Auth::User()->role == 'manager'
                    ? User::where('organization_id', Auth::User()->organization_id)
                    ->where('role', 'passenger')
                    ->whereBetween('created_at', [now()->subMonth(), now()])
                    ->count()
                    : User::where('role', 'passenger')
                    ->whereBetween('created_at', [now()->subMonth(), now()])
                    ->count();
        
        $new_drivers=Auth::User()->role == 'manager'
                    ? User::where('organization_id', Auth::User()->organization_id)
                    ->where('role', 'driver')
                    ->whereBetween('created_at', [now()->subMonth(), now()])
                    ->count()
                    : User::where('role', 'driver')
                    ->whereBetween('created_at', [now()->subMonth(), now()])
                    ->count();

        $current_month=now()->month;
        
        $passenger_count=Auth::User()->role == 'manager' ? User::where('organization_id', Auth::User()->organization_id)
                        ->where('role', 'passenger')
                        ->count()
                        : User::where('role', 'passenger')
                        ->count();

        $driver_count=Auth::User()->role == 'manager' ? User::where('organization_id', Auth::User()->organization_id)
                    ->where('role', 'driver')
                    ->count()
                    : User::where('role', 'driver')
                    ->count();
                               
        return [
            Stat::make('TotalPassengers',$passenger_count)
            ->descriptionIcon($new_passengers > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
            ->color($new_passengers > 0 ? 'success' : 'GRAY')
            ->description($new_passengers),
            
            Stat::make('TotalDrivers',$driver_count)
            ->descriptionIcon($new_drivers > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
            ->color($new_drivers > 0 ? 'success' : 'gray')
            ->description($new_drivers),

            Stat::make(
                'TotalMonthlyTrips',
                Auth::user()->role == 'manager'
                ? BusMovement::where('organization_id',Auth::user()->organization_id)
                ->whereMonth('actual_start',$current_month)
                ->count()
                : BusMovement::whereMonth('actual_start',$current_month)
                ->count()
            )
        ];
    }
}
