<?php

namespace App\Jobs;

use App\Models\product;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostProductsToFacebook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FacebookService $facebook)
    {
        $products = product::get(); 

        foreach ($products as $product) {
            $message = "📦 New Product: {$product->title}\n{$product->description}\nPrice: {$product->price}buy it now   -> " . url( "product/" . $product->id);

    
            $facebook->postImageToPage($message);
            sleep(1.5); 
        }
    }
}