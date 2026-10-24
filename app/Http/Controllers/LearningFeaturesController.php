<?php

namespace App\Http\Controllers;

class LearningFeaturesController extends Controller
{
    public function qudrat()
    {
        return view('home.learning.qudrat', ['topics' => [
            ['التناظر اللفظي', 'افهم العلاقة بين كلمتين، ثم ابحث عن العلاقة المشابهة.', 'verbal_analogies'],
            ['إكمال الجمل', 'اقرأ المعنى كاملًا واختر ما يجعل الجملة مترابطة.', 'sentence_completion'],
            ['الخطأ السياقي', 'ميّز الكلمة التي لا تنسجم مع معنى العبارة.', 'contextual_errors'],
            ['المفردة الشاذة', 'تعرّف على ما يجمع الكلمات وحدد المختلف بينها.', 'odd_word_out'],
            ['استيعاب المقروء', 'اقرأ نصًا، ثم تدرب على فهم فكرته وتفاصيله.', 'reading_comprehension'],
            ['الارتباط والاختلاف', 'لاحظ الروابط والفروق الدقيقة بين المعاني.', 'association_and_difference'],
        ]]);
    }

    public function arabicSkills()
    {
        return view('home.learning.arabic-skills', ['banks' => [
            ['نَسَق', 'النحو وبناء الجملة', 'افهم وظيفة الكلمة في الجملة والعلاقات بين أجزائها.', 'sentence_completion'],
            ['رَسْم', 'الإملاء والتحرير الكتابي', 'راجع رسم الكلمات وعلامات الترقيم في سياقها.', 'contextual_errors'],
            ['بَيان', 'البلاغة وتذوق النصوص', 'تعرّف على الصور والأساليب وكيف تصنع المعنى.', 'reading_comprehension'],
            ['مِداد', 'الكتابة الأدبية', 'تدرّب على فهم السرد والحوار والوصف والتعبير.', 'verbal_analogies'],
            ['بُرهان', 'الكتابة العلمية', 'ميّز التنظيم والدقة والتوثيق في النص العلمي.', 'association_and_difference'],
        ]]);
    }
}
