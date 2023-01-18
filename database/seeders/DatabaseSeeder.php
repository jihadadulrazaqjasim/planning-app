<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
<<<<<<< HEAD
        // \App\Models\User::factory(10)->create();

=======
        // \App\Models\Tester::factory(10)->create();
        \App\Models\User::factory(10)->create();
        \App\Models\Board::factory(10)->create();
        \App\Models\Task::factory(10)->create();
        \App\Models\Lable::factory(10)->create();
        \App\Models\Status::factory(10)->create();
        // \App\Models\User::factory(10)->create();

        // \App\Models\Developer::factory(10)->create();
        // \App\Models\Owner::factory(10)->create();
        
>>>>>>> 2d543dc75c5dc73c9fb085a26b5bc57c494fbbf1
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
