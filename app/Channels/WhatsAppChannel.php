<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toWhatsApp')) {
            return $notification->toWhatsApp($notifiable);
        }
    }
}
