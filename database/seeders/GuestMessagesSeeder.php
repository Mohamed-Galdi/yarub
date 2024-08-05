<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuestMessagesSeeder extends Seeder
{
    public function run()
    {
        $arabicMessages = [
            'مرحبا، هل يمكنني معرفة المزيد عن دوراتكم؟',
            'شكرا لكم على هذه المنصة الرائعة لتعلم اللغة العربية',
            'متى تبدأ الدورة القادمة؟',
            'هل لديكم دروس للمبتدئين؟',
            'أحب طريقتكم في التدريس',
            'هل يمكنني الحصول على معلومات عن الأسعار؟',
            'كيف يمكنني التسجيل في الدورة المتقدمة؟',
            'هل توفرون شهادات بعد إكمال الدورة؟',
            'أواجه مشكلة في الوصول إلى المواد التعليمية',
            'هل لديكم دروس خاصة؟'
        ];

        $arabicNames = [
            'محمد', 'أحمد', 'فاطمة', 'مريم', 'علي', 'خالد', 'عائشة', 'زينب', 'عمر', 'حسن'
        ];

        for ($i = 0; $i < 10; $i++) {
            $source = rand(0, 1) ? 'about_page' : 'contact_page';

            DB::table('guest_messages')->insert([
                'message' => $arabicMessages[$i],
                'source' => $source,
                'name' => $source === 'about_page' ? null : $arabicNames[array_rand($arabicNames)],
                'email' => $source === 'about_page' ? null : Str::random(10) . '@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
