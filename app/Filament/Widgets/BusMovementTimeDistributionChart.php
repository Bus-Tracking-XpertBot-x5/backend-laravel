<?php

namespace App\Filament\Widgets;

use App\Models\BusMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Auth;

class BusMovementTimeDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Bus Movement Time Distribution';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $timeSlots = [
            'Early Morning (5-8 AM)',
            'Morning (8-11 AM)',
            'Noon (11 AM-2 PM)',
            'Afternoon (2-5 PM)',
            'Evening (5-8 PM)',
            'Night (8-11 PM)',
        ];

        $data = collect($timeSlots)->map(function ($label, $index) {
            $startHour = $index * 3 + 5;
            $endHour = $startHour + 3;

            $query = BusMovement::query();
            
            if (Auth::User()->role === 'manager') {
                $query->where('organization_id', Auth::User()->organization_id);
            }

            return $query->where('status', 'completed')
                ->whereRaw("HOUR(actual_start) >= ? AND HOUR(actual_start) < ?", [$startHour, $endHour])
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Completed Trips',
                    'data' => $data->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => '#3B82F6',
                    'pointBackgroundColor' => '#3B82F6',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => '#3B82F6',
                ],
            ],
            'labels' => $timeSlots,
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}