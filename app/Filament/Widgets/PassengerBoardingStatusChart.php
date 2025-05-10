<?php

namespace App\Filament\Widgets;

use App\Models\PassengerBoarding;
use Filament\Widgets\ChartWidget;
use Auth;

class PassengerBoardingStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Passenger Boarding Status';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $statuses = ['scheduled', 'boarded', 'missed', 'departed'];
        $data = collect($statuses)->map(function ($status) {
            $query = PassengerBoarding::query();
            
            if (Auth::User()->role === 'manager') {
                $query->whereHas('movement', function ($q) {
                    $q->where('organization_id', Auth::User()->organization_id);
                });
            }
            
            return $query->where('status', $status)->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Passengers',
                    'data' => $data->toArray(),
                    'backgroundColor' => [
                        '#F59E0B', // scheduled - amber
                        '#10B981', // boarded - green
                        '#EF4444', // missed - red
                        '#6366F1', // departed - indigo
                    ],
                ],
            ],
            'labels' => collect($statuses)->map(fn ($status) => ucfirst($status))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
} 