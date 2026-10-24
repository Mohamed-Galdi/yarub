@extends('layouts.guest')
@section('title', 'مهارات اللغة العربية | منصة يعرب')
@section('description', 'خمسة بنوك للتدرّب على النحو والإملاء والبلاغة والكتابة، مع تدريب سريع ونماذج ومراجعة للإجابات. كل ما تحتاجه هو حساب طالب في يعرب.')
@section('content')
<div class="learning-page learning-arabic" data-learning-page>
    <div class="learning-hero learning-container">
        <div class="learning-hero-copy"><h1>مهارات اللغة العربية.<br><span>من فهم الجملة<br>إلى دقّة التعبير.</span></h1><p class="learning-lead">خمسة بنوك تجمع النحو والإملاء والبلاغة والكتابة. اختر مهارتك، وجرّب، واكتشف من الشرح ما الذي يجعل الإجابة أدقّ.</p><x-learning.access destination="student.arabic-skills.index" label="مهارات اللغة العربية" /><a class="learning-jump" href="#content">استكشف البنوك الخمسة ↓</a></div>
        <x-learning.demo :matching="true" />
    </div>
    <div class="learning-facts"><div class="learning-container"><p><strong>1,500 سؤال أساسي</strong> في خمسة بنوك</p><p>اختيار من متعدد · صح أو خطأ · مطابقة</p><p>تدريب سريع أو نموذج كامل</p></div></div>
    <section class="learning-container learning-section" id="content" aria-labelledby="content-heading">
        <div class="learning-section-intro"><h2 id="content-heading">لكل مهارة<br>مساحة للتدرّب.</h2><p>في كل بنك 300 سؤال أساسي، موزّعة على عشرة نماذج أصلية. ابدأ بما تريد تقويته، ثم وسّع تجربتك إلى المهارات الأخرى.</p></div>
        <div class="learning-topic-list">@foreach($banks as [$title, $subtitle, $description, $icon])<article><x-qudrat.category-icon :type="$icon" /><div><h3>{{ $title }} <span>{{ $subtitle }}</span></h3><p>{{ $description }}</p></div><span class="learning-topic-count">300 سؤال أساسي</span></article>@endforeach</div>
    </section>
    <section class="learning-formats learning-container learning-section"><div class="learning-section-intro"><h2>وقتك قصير؟<br>أو تريد نموذجًا كاملًا؟</h2><p>طريقتان للتدريب من البنك نفسه، مع نتيجة محسوبة بالنقاط وشرح للإجابات.</p></div><div class="learning-format-options"><article><span class="learning-format-number">10</span><div><h3>تدريب سريع</h3><p>عشرة أسئلة مختلفة، بحد أقصى 20 نقطة. يبدأ بالأسئلة التي لم تجب عنها، ثم يراجع ما يحتاج إلى تدريب وفق أحدث إجابة لك.</p></div></article><article><span class="learning-format-number">30</span><div><h3>نموذج كامل</h3><p>ثلاثون سؤالًا مرتبة من النموذج، بحد أقصى 60 نقطة. اختر واحدًا من عشرة نماذج أصلية في كل بنك.</p></div></article></div></section>
    <section class="learning-flow"><div class="learning-container learning-section"><div class="learning-section-intro"><h2>تعلّم من الإجابة،<br>ولا تكتفِ بالنتيجة.</h2><p>واجهة واضحة، وسجلّ يرجع معك كل مرة.</p></div><ol class="learning-steps"><li><h3>اختر وأرسل إجابتك</h3><p>خيارات واضحة، ومطابقة مستقلة لكل عبارة. في المطابقة، كل جزء صحيح يمنحك نقطة حتى لو أخطأت في الجزء الآخر.</p></li><li><h3>اقرأ التفسير قريبًا منك</h3><p>بعد الإرسال تظهر الإجابة الصحيحة وشرحها بجوار السؤال، ثم تنتقل عندما تكون مستعدًا.</p></li><li><h3>تابع من حيث توقّفت</h3><p>استأنف تدريبك الجاري، وارجع إلى نتائج التدريبات المكتملة وإجاباتك من سجلّ البنك.</p></li></ol></div></section>
    <section class="learning-container learning-end"><div><h2>حساب الطالب<br>هو بداية التدريب.</h2><p>سجّل الدخول لتجد البنوك الخمسة في قسم «مهارات اللغة العربية». لا يلزم شراء دورة أو اشتراك في الشروحات.</p></div><x-learning.access destination="student.arabic-skills.index" label="مهارات اللغة العربية" /></section>
    <aside class="learning-container learning-cross"><p>تستعدّ لأسئلة القدرات اللفظية؟</p><a href="{{ route('qudrat') }}">اكتشف تدريب القدرات ←</a></aside>
</div>
@endsection
