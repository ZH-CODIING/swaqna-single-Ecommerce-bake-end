<?php

namespace App\Notifications;

use App\Services\WhatsAppService;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;


class AdminOrderPlaced extends Notification
{


    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function via($notifiable)
    {
        return [
            \App\Channels\WhatsAppChannel::class,
            'database',
            'mail'
        ];
    }

    public function toWhatsApp($notifiable)
    {
        $phone = $notifiable->phone;

        $message = "تم وصول طلب جديد :   #{$this->orderId}";

        return WhatsAppService::sendMessage($phone, $message);
    }



    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->orderId,
            'message' => "تم وصول طلب جديد  #{$this->orderId}",
            'url' => url("/orders/{$this->orderId}"),
        ];
    }
    public function toMail($notifiable)
    {
        $notifiable->email = 'walid.reda345@gmail.com';
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('New Order Received: #' . $this->orderId)
            ->view('emails.order_confirmed_admin', ['order_id' => $this->orderId]);
    }
}
