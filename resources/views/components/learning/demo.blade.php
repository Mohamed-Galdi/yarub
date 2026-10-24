@props(['matching' => false])
<section class="learning-demo" data-learning-demo data-demo-type="{{ $matching ? 'matching' : 'choice' }}" aria-labelledby="demo-heading">
    <div class="learning-demo-bar"><span>تجربة من شكل التدريب</span><span>{{ $matching ? 'مطابقة' : 'تناظر لفظي' }}</span></div>
    <div class="learning-demo-body">
        <h2 id="demo-heading">{{ $matching ? 'صِل كل كلمة بوظيفتها في الجملة.' : 'أيّ زوج يشبه العلاقة: القلم : الكتابة؟' }}</h2>
        <p class="learning-demo-note">مثال توضيحي للتجربة؛ لا يُحفظ في سجلّك.</p>
        <form data-demo-form>
            @if($matching)
                <p class="learning-demo-sentence">«قرأ الطالبُ كتابًا.»</p>
                <label class="learning-demo-match">الطالبُ<select name="subject" required><option value="">اختر الوظيفة</option><option value="subject">فاعل</option><option value="object">مفعول به</option></select></label>
                <label class="learning-demo-match">كتابًا<select name="object" required><option value="">اختر الوظيفة</option><option value="subject">فاعل</option><option value="object">مفعول به</option></select></label>
                <p class="learning-demo-note">اختر لكل كلمة بشكل مستقل؛ لكل مطابقة صحيحة نقطة.</p>
            @else
                <fieldset><legend class="sr-only">اختر الإجابة الصحيحة</legend>
                    @foreach(['correct' => 'المفتاح : الفتح', 'place' => 'الكتاب : المكتبة', 'opposite' => 'الليل : النهار', 'part' => 'الشجرة : الغصن'] as $value => $text)
                        <label class="learning-demo-choice"><input type="radio" name="answer" value="{{ $value }}" required><span>{{ $text }}</span></label>
                    @endforeach
                </fieldset>
            @endif
            <button type="submit" class="learning-button" data-demo-submit hidden>تحقّق من الإجابة</button>
            <noscript><p class="learning-demo-note">فعّل JavaScript لتجربة السؤال هنا، أو سجّل الدخول لبدء التدريب.</p></noscript>
        </form>
        <div class="learning-demo-feedback" data-demo-feedback role="status" tabindex="-1" hidden></div>
        <button class="learning-text-button" type="button" data-demo-reset hidden>جرّب مرة أخرى</button>
    </div>
</section>
