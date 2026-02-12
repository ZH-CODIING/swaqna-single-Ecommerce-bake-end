<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $visits = [];
        for ($i = 0; $i < 100_000; $i++) {
            $visits[] = [
                'resource_type' => rand(1, 2),
                'resource_id' => 4,
                'visited_at' => now()->subDays(rand(0, 7)),
            ];
        }
        collect($visits)->chunk(1000)->each(function ($chunk) {
            \DB::table('visits')->insert($chunk->toArray());
        });

        $this->command->info('100,000 visits seeded successfully!');
    }
}
