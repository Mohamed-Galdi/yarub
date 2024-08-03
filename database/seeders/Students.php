<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class Students extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        for ($i = 1; $i <= 10; $i++) {
            $gender = $i % 2 == 0 ? 'male' : 'female';
            $imageNumber = ($i % 5) + 1; // This will give you numbers from 1 to 5 cyclically

            $name = $faker->name($gender);
            $email = $faker->unique()->safeEmail();
            $password = bcrypt($faker->password); // It's better to hash the password
            $image = $gender == 'male'
                ? "storage/users_avatars/boy_{$imageNumber}.jpg"
                : "storage/users_avatars/girl_{$imageNumber}.jpg";

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'student',
                'avatar' => $image,
            ]);
        }
    }
}
