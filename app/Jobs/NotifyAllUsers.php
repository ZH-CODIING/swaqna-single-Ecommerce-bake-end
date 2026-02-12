<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAllUsers
{
    use Dispatchable, SerializesModels;

    protected string $message;
    protected string $subject;

    public function __construct(string $message, string $subject)
    {
        $this->message = $message;
        $this->subject = $subject;
    }

    public function handle(FacebookService $facebook)
    {

        if (env('email_marketing_enabled') == 'false') {
            return;
        }
        User::where('role', 'user')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->notify(new AdminBroadcastNotification($this->message, $this->subject , $user));
                sleep(1);
            }
        });
    }
}
