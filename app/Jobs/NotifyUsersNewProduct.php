<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Product;
use App\Notifications\NewProductNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUsersNewProduct implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     *
     * @return void
     */
    public function handle(): void
    {

        if (env('email_marketing_enabled') === 'false') {
            return;
        }
        User::where('role', 'user')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $user->notify(new NewProductNotification($this->product, $user));
                }
            });
    }
}