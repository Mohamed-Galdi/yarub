<?php

namespace Database\Seeders;

use App\Models\HomePage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = [
            'main_title' => 'منصتك الرقمية الأولى لتعلم اللغة العربية',
            'sub_title' => 'مهمتنا تسهيل تعلم اللغة العربية لمختلف المستويات بشكل رقمي، سهل، وفعال.',
            'our_features_title' => 'ميزاتنا الاساسية',
            'first_feature_title' => 'منصة رقمية',
            'first_feature_content' => 'منصة رقمية تتيح لك تعلم اللغة العربية في بيئة حديثة ومتطورة',
            'second_feature_title' => 'أعلى جودة ',
            'second_feature_content' => 'دروس ودورات بمواد تعليمية من طرف مؤطرين بأعلى جودة الكفاءات في اللغة العربية',
            'third_feature_title' => 'مواكبة مستمرة',
            'third_feature_content' => 'مواكبة مستمرة خلال مراحل التعلم من الدروس حتى الاختبارات',
            'last_section_title' => 'إنضم الى منصة يعرب',
            'last_section_content' => 'و إبدأ رحلة في تعلم دروس اللغة العربية من خلال منصة يعرب التي توفر حلول 100% رقمية لتسهيل مسارك في التعلم. و بإشراف أجود المؤطرين في المجال، و دعم متجاوب و سريع و باللغة العربية',
        ];

        HomePage::create($content);
    }
}
