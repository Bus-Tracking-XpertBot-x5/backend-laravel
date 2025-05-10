<?php

namespace App\Filament\Widgets;

use App\Models\BusMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Auth;

/**
 * A polar area chart showing the distribution of trip durations.
 * Each segment represents a 30-minute interval, with the size indicating
 * the number of trips in that duration range.
 */
class TripDurationChart extends ChartWidget
{
    protected static ?string $heading = 'Trip Duration Distribution';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $query = BusMovement::where('status', 'completed');

        if (Auth::user()->role === 'manager') {
            $query->where('organization_id', Auth::user()->organization_id);
        }

        $movements = $query->get()
            ->map(function ($movement) {
                return Carbon::parse($movement->actual_start)
                    ->diffInMinutes(Carbon::parse($movement->actual_end));
            })
            ->groupBy(function ($duration) {
                // Group by 30-minute intervals
                return floor($duration / 30) * 30;
            })
            ->map(function ($group) {
                return $group->count();
            });

        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(16, 185, 129, 0.8)',   // Green
            'rgba(245, 158, 11, 0.8)',   // Yellow
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(139, 92, 246, 0.8)',   // Purple
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(14, 165, 233, 0.8)',   // Sky
            'rgba(234, 179, 8, 0.8)',    // Amber
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Number of Trips',
                    'data' => $movements->values()->toArray(),
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.8', '1', $color);
                    }, $colors),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $movements->keys()->map(function ($duration) {
                return $duration . ' min';
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'legend' => [
                    'position' => 'right',
                ],
            ],
            'scales' => [
                'r' => [
                    'angleLines' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'display' => false,
                    ],
                    'suggestedMin' => 0,
                ],
            ],
        ];
    }
} 