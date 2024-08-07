<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutPage::create([
            'our_team_content' => 'تدار منصة يعُرب بأيدي خبراء في اللغة العربية حاصلين على على جوائز محلية ودولية بالإضافة إلى فريق فريق من التقنيين الذين يبذلون جل وقتهم في تشغيل المنصة على أعلى كفاءة وبشكل دوري ويعملون على تطويرها لألفضل إلى جانب دعم فني لا يتوانى عن تلبية كل متطلباتكم وعلى مدار الساعة',
            'our_goal_content' => 'رفع المستوى التحصيلي للطلاب والطاالبات في اختبارات القدرات المحلية الجزء اللفظي حصول معلمي و معلمات اللغة العربية على درجات عالية في اختبارات الرخصة المهنية رفع المستوى الاستيعابي والاعتماد على الذاكرة الدائمة في شروحات مواد اللغة العربية للمرحلة الثانوية'
        ]);
    }
}
