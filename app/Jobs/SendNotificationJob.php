<?php

namespace App\Jobs;

use App\Http\Controllers\NotificationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deviceToken;
    public $title;
    public $body;

    public function __construct($deviceToken, $title, $body)
    {
        $this->deviceToken = $deviceToken;
        $this->title = $title;
        $this->body = $body;
    }

    public function handle()
    {
        NotificationController::sendNotification($this->deviceToken, $this->title, $this->body);
    }
}
