<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Kreait\Firebase\Factory;

use Kreait\Firebase\Messaging\CloudMessage;

use Kreait\Firebase\Messaging\Notification;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationController extends Controller
{

    public static function sendNotification($deviceToken, string $title, string $body)
    {
        // Initialize Firebase with service account credentials
        $firebase = (new Factory)->withServiceAccount(env('FIREBASE_CREDENTIALS'));

        // Get Firebase Messaging instance
        $messaging = $firebase->createMessaging();

        // Ensure the device token is valid
        if (empty($deviceToken)) {
            return response()->json(['error' => 'Device token is missing'], 400);
        }

        // Create the notification
        $notification = Notification::create($title, $body);

        // Build the message
        $message = CloudMessage::withTarget('token', $deviceToken)->withNotification($notification);

        try {
            // Send the notification
            $messaging->send($message);
            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            return response()->json(['error' => 'Invalid message: ' . $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error sending notification: ' . $e->getMessage()], 500);
        }
    }
}
