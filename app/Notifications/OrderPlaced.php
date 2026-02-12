<?php

namespace App\Notifications;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class OrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function via($notifiable)
    {
     //  return [\App\Channels\WhatsAppChannel::class, 'mail'];
       return [\App\Channels\WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        $phone = $notifiable->phone;
        $message = "شكراً لك تم إكتمال  الطلب رقم #{$this->orderId}";



        $service = new WhatsAppService();

        return $service->sendMessage($phone, $message);
    }
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("تاكيد الطلب رقم #{$this->orderId}")
            ->greeting("مرحبا {$notifiable->name},")
            ->line("لقد تم إكتمال الطلب رقم #{$this->orderId}")
            ->action('عرض الطلب ', url("/orders/{$this->orderId}"))
            ->line('شكرا لك للتعامل معنا ');
    }
}
