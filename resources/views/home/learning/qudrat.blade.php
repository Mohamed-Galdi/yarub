@extends('layouts.guest')
@section('title', 'القدرات اللفظية | منصة يعرب')
@section('description', 'تعرّف على تدريب القدرات اللفظية في يعرب: ستة أقسام، جلسات قصيرة، تفسير للإجابات ومتابعة للتقدّم. ادخل بحساب الطالب لتبدأ.')
@section('content')
<div class="learning-page" data-learning-page>
    <div class="learning-hero learning-container">
        <div class="learning-hero-copy"><h1>القدرات اللفظية.<br><span>افهم العلاقة،<br>ثم اختر بثقة.</span></h1><p class="learning-lead">تدريب يحوّل السؤال من تخمين إلى فهم: اختر القسم، أجب، واقرأ لماذا كانت الإجابة صحيحة.</p><x-learning.access destination="student.qudrat.index" label="القدرات اللفظية" /><a class="learning-jump" href="#content">تعرّف على الأقسام الستة ↓</a></div>
        <x-learning.demo />
    </div>
    <div class="learning-facts"><div class="learning-container"><p><strong>1,200 سؤال أساسي</strong> موزّعة على ستة أقسام</p><p>جلسات قصيرة، وتفسير بعد كل إجابة</p><p>تقدّم محفوظ في حساب الطالب</p></div></div>
    <section class="learning-container learning-section" id="content" aria-labelledby="content-heading">
        <div class="learning-section-intro"><h2 id="content-heading">ست طرق لقراءة<br>المعنى بدقّة أكبر.</h2><p>اختر مهارة تريد العمل عليها. كل قسم يبدأ ببنك من 200 سؤال، لتتدرّب على نوع السؤال بدل التنقّل العشوائي بين الأنواع.</p></div>
        <div class="learning-topic-list">@foreach($topics as [$title, $description, $icon])<article><x-qudrat.category-icon :type="$icon" /><div><h3>{{ $title }}</h3><p>{{ $description }}</p></div><span class="learning-topic-count">200 سؤال أساسي</span></article>@endforeach</div>
    </section>
    <section class="learning-flow"><div class="learning-container learning-section"><div class="learning-section-intro"><h2>من السؤال<br>إلى فهمه.</h2><p>هكذا تبدو جلستك داخل حساب الطالب.</p></div><ol class="learning-steps"><li><h3>اختر قسمك</h3><p>ابدأ جلسة من نحو عشرة أسئلة. تبقى أسئلة النص القرائي معًا، وقد يزيد عددها قليلًا.</p></li><li><h3>أجب، ثم افهم السبب</h3><p>اختر إجابتك واعرض التصحيح والتفسير. ويمكنك طلب تلميح يساعدك على التفكير.</p></li><li><h3>ارجع إلى ما تعلّمته</h3><p>تابع الجلسة من موضعك، وراجع الأسئلة السابقة. ستجد تقدّمك ودقة إجابتك الأولى في كل قسم.</p></li></ol></div></section>
    <section class="learning-container learning-end"><div><h2>الخطوة التالية بسيطة:<br>ادخل بحسابك كطالب.</h2><p>لا تحتاج إلى شراء دورة أو الاشتراك في الشروحات للوصول إلى هذا التدريب.</p></div><x-learning.access destination="student.qudrat.index" label="القدرات اللفظية" /></section>
    <aside class="learning-container learning-cross"><p>تريد أن تتدرّب على العربية أيضًا؟</p><a href="{{ route('arabic-skills') }}">اكتشف مهارات اللغة العربية ←</a></aside>
</div>
@endsection
