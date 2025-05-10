<?php

namespace App\Filament\Widgets;

use App\Models\BusMovement;
use Filament\Widgets\ChartWidget;
use Auth;

class BusMovementStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Bus Movement Status';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        $data = collect($statuses)->map(function ($status) {
            $query = BusMovement::query();
            
            if (Auth::User()->role === 'manager') {
                $query->where('organization_id', Auth::User()->organization_id);
            }
            
            return $query->where('status', $status)->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Movements',
                    'data' => $data->toArray(),
                    'backgroundColor' => [
                        '#F59E0B', // scheduled - amber
                        '#3B82F6', // in_progress - blue
                        '#10B981', // completed - green
                        '#EF4444', // cancelled - red
                    ],
                ],
            ],
            'labels' => collect($statuses)->map(fn ($status) => ucfirst(str_replace('_', ' ', $status)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
} 