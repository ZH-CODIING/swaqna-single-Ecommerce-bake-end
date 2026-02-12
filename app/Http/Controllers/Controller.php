<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

abstract class Controller
{
    public function ResetCache($key)
    {
        Cache::forget($key);
    }

    public function SetIntialVersion($key)
    {
        if (!Cache::has($key)) {
            Cache::put($key, 1);
        } else {
            Cache::increment($key);
        }
    }
}
