<?php

namespace Database\Seeders;

use App\Models\TestAttempt as ModelsTestAttempt;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestAttempt extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attempts = [
            [
                'user_id' => 5,
                'test_id' => 1,
                'score' => 20,
                'answers' => [
                    "1" => 1,
                    '2' => 4,
                    '3' => 2,
                    '4' => 2,
                    '5' => 3,
                ],
            ],
            [
                'user_id' => 8,
                'test_id' => 1,
                'score' => 40,
                'answers' => [
                    "1" => 2,
                    '2' => 2,
                    '3' => 4,
                    '4' => 3,
                    '5' => 1,
                ],
            ],
            [
                'user_id' => 12,
                'test_id' => 1,
                'score' => 60,
                'answers' => [
                    "1" => 3,
                    '2' => 2,
                    '3' => 4,
                    '4' => 1,
                    '5' => 3,
                ],
            ],

        ];

        foreach ($attempts as $attempt) {
            ModelsTestAttempt::create($attempt);
        }
    }
}
