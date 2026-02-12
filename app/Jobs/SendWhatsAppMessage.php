<?php
namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;
    protected $file;
    protected $filename;

    public function __construct($phone, $message, $file = null, $filename = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->file = $file;
        $this->filename = $filename;
    }

    public function handle()
    {
        WhatsAppService::sendMessage($this->phone, $this->message, );
    }
}
