<?php

namespace App\Filament\Widgets;

use App\Models\BusMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Auth;

class AverageRevenuePerTripChart extends ChartWidget
{
    protected static ?string $heading = 'Average Revenue Per Trip';
    protected static ?int $sort = 4;

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

            $movements = $query->with(['passengerBoardings' => function($query) {
                    $query->whereIn('status', ['boarded', 'departed']);
                }])
                ->get();

            if ($movements->isEmpty()) {
                return 0;
            }

            $totalRevenue = $movements->sum(function ($movement) {
                return $movement->passengerBoardings->count() * $movement->price;
            });

            return round($totalRevenue / $movements->count(), 2);
        });

        return [
            'datasets' => [
                [
                    'label' => 'Average Revenue',
                    'data' => $data->toArray(),
                    'backgroundColor' => '#8B5CF6',
                    'borderColor' => '#8B5CF6',
                ],
            ],
            'labels' => collect(range(0, 11))->map(fn ($month) => Carbon::now()->startOfYear()->addMonths($month)->format('M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getContentHeight(): ?string
    {
        return '300px';
    }
} 