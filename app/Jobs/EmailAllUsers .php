<?php

namespace App\Jobs;

use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmailAllUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $mail_HTML;
    protected string $subject;

    public function __construct(string $subject, string $mail_HTML)
    {
        $this->mail_HTML = $mail_HTML;
        $this->subject = $subject;
    }
    public function handle()
    {

        if (env('email_marketing_enabled') == 'false') {
            return;
        }
        User::where('role', 'user')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $passData = [$user, $this->mail_HTML, $this->subject];
                Mail::raw([], function ($message) use ($passData) {
                    $message->to($passData[0])
                        ->subject($passData[2])
                        ->setBody($passData[1], 'text/html');
                });
            }
        });
    }
}
