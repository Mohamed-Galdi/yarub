<?php

namespace Database\Seeders;

use App\Models\ContactPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactPage::create([
            'commercial_registration_no' => '4030408810',
            'phone_number' => '+966 5 0004 4502',
            'email' => 'yarubsa25@gmail.com',
            'address' => 'جدة، المملكة العربية السعودية',
            'whatsapp_number' => 'https://wa.me/0539867197',
            'instagram' => 'https://www.instagram.com/yarub_ar/',
            'tiktok' => 'https://www.tiktok.com/@yarub_ar',
            'snapchat' => 'https://www.snapchat.com/add/yarub_ar',
        ]);
    }
}
