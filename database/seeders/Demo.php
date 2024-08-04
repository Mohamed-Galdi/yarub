<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Demo extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Demo Admin --------------------------------------------------------------------------------
        $Admin = [
            'id' => 1,
            'name' => 'DemoAdmin',
            'email' => 'admin@demo.com',
            'password' => '00000000',
            'role' => 'admin'
        ];
        User::create($Admin);

        // Demo Student --------------------------------------------------------------------------------
        $Student = [
            'name' => 'طالبة  افتراضية',
            'email' => 'student@demo.com',
            'password' => '00000000',
            'role' => 'student',
            'avatar'=> 'storage/users_avatars/girl_6.jpg'
        ];
        User::create($Student);
    }
}
