<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

//         \App\Models\User::factory()->create([
//             'name' => 'admin',
//             'code' => 'admin',
//             'email' => 'admin@gmail.com',
//             'active' => '1',
//             'password' => bcrypt(12345),
//         ]);

        \App\Models\ReviewCar::factory()->create([
            'content' => 'xe đẹp giá re',
            'star' => 4,
            'customer_id' => '18',
            'car_id' => '2',
        ]);

        \App\Models\ReviewCar::factory()->create([
            'content' => 'xe đẹp giá rẻ, chất lượng cao',
            'star' => 5,
            'customer_id' => '14',
            'car_id' => '2',
        ]);
    }
}
