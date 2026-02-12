<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsAppLimitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('whatsapp_limits')->insert(
            [
                'remaining_messages' => 0 ,
                'status' => 0
            ]
        );
    }
}
