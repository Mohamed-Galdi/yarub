<?php

namespace Database\Seeders;

use App\Models\Partners;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnersPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = [
            [
                'name' => 'مؤسسة سراة بروداكشن',
                'url' => 'https://google.com/'
            ],
            [
                'name' => 'منصة وجس للتقنيات',
                'url' => 'https://wajasportal.com/'
            ],
            [
                'name' => 'رؤية السعودية 2030',
                'url' => 'https://www.vision2030.gov.sa/ar/'
            ]
        ];

        foreach ($partners as $partner) {
            $p = new Partners();
            $p->name = $partner['name'];
            $p->url = $partner['url'];
            $p->save();
        }
    }
}
