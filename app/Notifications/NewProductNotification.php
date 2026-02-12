<?php
namespace App\Notifications;


use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

use App\Models\Product;
use App\Models\User;

class NewProductNotification extends Notification 
{
    

    public $product;
    public $user;

    public function __construct(Product $product, User $user)
    {
        $this->product = $product;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🆕 New Product Added: ' . $this->product->name)
            ->view('emails.new_product_user', [
                'product' => $this->product,
                'user' => $this->user,
            ]);
    }
}