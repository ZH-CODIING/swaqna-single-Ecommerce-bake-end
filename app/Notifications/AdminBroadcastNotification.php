<?php

namespace App\Notifications;

use App\Services\WhatsAppService;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;


class AdminBroadcastNotification extends Notification
{


    protected $message;
    protected $title;
    protected $user;

    public function __construct($message, $title, $user)
    {
        $this->message = $message;
        $this->title = $title;
        $this->user = $user;
      
    }

    public function via($notifiable)
    {
        return [
            //      \App\Channels\WhatsAppChannel::class,
            'mail'
        ];
    }

    public function toWhatsApp($notifiable)
    {
        $phone = $notifiable->phone;
        $service = new WhatsAppService();
        return $service->sendMessage($phone, $this->message);
    }

    public function toMail($notifiable)
    {   
       
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.general_notify_template', [
                'title' => $this->title,
                'Mailmessage' => $this->message,
                'user_name' => $this->user->name,
            ]);
    }
}
