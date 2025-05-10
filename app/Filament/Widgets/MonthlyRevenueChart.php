<?php

namespace App\Filament\Widgets;

use App\Models\BusMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Auth;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = collect(range(0, 11))->map(function ($month) {
            $startOfMonth = Carbon::now()->startOfYear()->addMonths($month);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $query = BusMovement::where('status', 'completed')
                ->whereBetween('actual_start', [$startOfMonth, $endOfMonth]);

            if (Auth::User()->role === 'manager') {
                $query->where('organization_id', Auth::User()->organization_id);
            }

            $revenue = $query->with(['passengerBoardings' => function($query) {
                    $query->whereIn('status', ['boarded', 'departed']);
                }])
                ->get()
                ->sum(function ($movement) {
                    return $movement->passengerBoardings->count() * $movement->price;
                });

            return $revenue;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => '#10B981',
                ],
            ],
            'labels' => collect(range(0, 11))->map(fn ($month) => Carbon::now()->startOfYear()->addMonths($month)->format('M'))->toArray(),
        ];
    }

    protected function getContentHeight(): ?string
    {
        return '900px'; // or any height you want
    }
    


    protected function getType(): string
    {
        return 'line';
    }
} 
