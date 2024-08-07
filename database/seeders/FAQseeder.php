<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FAQseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'كيف يمكنني التسجيل في الدورة؟',
                'answer' => 'يمكنك التسجيل في الدورة من خلال زيارة موقعنا الإلكتروني والضغط على زر "التسجيل" في الصفحة الرئيسية. بعد ذلك، اتبع الخطوات المطلوبة لإكمال عملية التسجيل.'
            ],
            [
                'question' =>  'ما هي طريقة الدفع المتاحة؟',
                'answer' => 'نوفر عدة طرق للدفع مثل البطاقات الائتمانية، الدفع عبر Apple Pay، والتحويلات البنكية. يمكنك اختيار الطريقة التي تناسبك عند إتمام عملية الشراء.'
            ],
            [
                'question' =>  'هل يمكنني استرداد المبلغ المدفوع إذا لم أكن راضياً عن الدورة؟',
                'answer' => 'نعم، نوفر سياسة استرداد خلال 14 يومًا من تاريخ الشراء. إذا لم تكن راضياً عن الدورة، يمكنك تقديم طلب لاسترداد المبلغ عبر خدمة العملاء.'
            ],
            [
                'question' =>  'هل يمكنني الوصول إلى محتوى الدورة بعد انتهائها؟',
                'answer' => 'بمجرد تسجيلك في الدورة، سيكون لديك وصول إلى المحتوى لمدة ثلاثة أشهر بعد الدفع. يمكنك العودة إلى المواد والدروس في أي وقت تحتاج إليه خلال هذه الفترة.'
            ],
            [
                'question' =>  'هل هناك دعم فني متاح في حال واجهت مشاكل تقنية؟',
                'answer' => 'نعم، لدينا فريق دعم فني متاح على مدار الساعة لمساعدتك في حل أي مشكلة تقنية قد تواجهها. يمكنك التواصل معنا عبر البريد الإلكتروني أو الدردشة المباشرة.'
            ],
            [
                'question' =>  'كيف يمكنني تقييم مستواي في الدورة؟',
                'answer' => 'توفر المنصة إختبارات قبلية و بعدية تتيح للطالب و المشرفين تقييم المستواى'
            ],
        ];

        foreach ($faqs as $faq) {
            $f_a_q = new FAQ();
            $f_a_q->question = $faq['question'];
            $f_a_q->answer = $faq['answer'];
            $f_a_q->save();
        }
    }
}