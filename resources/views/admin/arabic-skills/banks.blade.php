@extends('layouts.admin')
@section('content')
<section data-qudrat-admin data-arabic-admin dir="rtl">
    @include('admin.arabic-skills._nav')
    <div class="qudrat-heading"><div><h2>البنوك والنماذج</h2><p>إيقاف البنك يمنع تدريبات جديدة ويحافظ على التدريبات الجارية والنتائج.</p></div></div>
    @foreach($banks as $bank)
        @php
            $bankFailed = $errors->any() && (string)old('bank_form') === (string)$bank->id;
            $modelFailed = $errors->any() && $bank->models->contains('id', old('model_form'));
        @endphp
        <article class="arabic-admin-bank">
            <h3>{{ $bank->title }} <span class="qudrat-note">{{ $bank->is_active ? 'متاح' : 'متوقف' }}</span></h3>
            <p>{{ $bank->subtitle }}</p>
            <dl class="qudrat-counts"><div><dt>الأسئلة</dt><dd>{{ $bank->questions_count }}</dd></div><div><dt>المنشورة</dt><dd>{{ $bank->active_count }}</dd></div><div><dt>المؤرشفة</dt><dd>{{ $bank->questions_count - $bank->active_count }}</dd></div></dl>
            <div class="qudrat-actions"><a class="qudrat-table-link" href="{{ route('admin.arabic-skills.questions.index', ['bank_id' => $bank->id]) }}">أسئلة {{ $bank->title }}</a><a class="qudrat-table-link" href="{{ route('admin.arabic-skills.questions.create', ['bank_id' => $bank->id]) }}">إضافة سؤال إلى {{ $bank->title }}</a></div>
            <details @if($bankFailed) open @endif><summary>إعدادات {{ $bank->title }}</summary>
                <form class="qudrat-form qudrat-editor" action="{{ route('admin.arabic-skills.banks.update', $bank) }}" method="POST">@csrf @method('PATCH')
                    <input type="hidden" name="bank_form" value="{{ $bank->id }}"><input type="hidden" name="revision" value="{{ $bankFailed ? old('revision') : $editor->revision($bank) }}">
                    @if($bankFailed && $errors->has('revision'))<p class="qudrat-note">حُفظت مدخلاتك أدناه للمراجعة. انسخ ما تحتاجه ثم أعد تحميل الصفحة قبل الحفظ. العنوان الحالي: {{ $bank->title }}؛ الموضوع الحالي: {{ $bank->subtitle }}.</p>@endif
                    <label>عنوان البنك<input name="title" value="{{ $bankFailed ? old('title') : $bank->title }}" maxlength="255" required></label>
                    <label>موضوع البنك<input name="subtitle" value="{{ $bankFailed ? old('subtitle') : $bank->subtitle }}" maxlength="255" required></label>
                    <div class="qudrat-grid"><label>ترتيب العرض<input type="number" name="sort_order" min="1" max="65535" value="{{ $bankFailed ? old('sort_order') : $bank->sort_order }}" required></label><label>إتاحة البنك<select name="is_active"><option value="1" @selected((string)($bankFailed ? old('is_active') : (int)$bank->is_active) === '1')>متاح</option><option value="0" @selected((string)($bankFailed ? old('is_active') : (int)$bank->is_active) === '0')>متوقف</option></select></label></div>
                    <button class="qudrat-button">حفظ إعدادات {{ $bank->title }}</button>
                </form>
            </details>
            <details @if($modelFailed) open @endif><summary>النماذج الأصلية في {{ $bank->title }} ({{ $bank->models->count() }})</summary>
                <p class="qudrat-note">عضوية النماذج ثابتة. يتاح النموذج للطالب عند نشر جميع أسئلته الثلاثين وإتاحة البنك.</p>
                @foreach($bank->models as $model)
                    @php
                        $thisModelFailed = $modelFailed && (string)old('model_form') === (string)$model->id;
                    @endphp
                    <div class="arabic-admin-model">
                        <div><h4>{{ $model->title }}</h4><p>{{ $model->active_count }} من {{ $model->questions_count }} سؤال منشور · {{ $bank->is_active && $model->questions_count === 30 && $model->active_count === 30 ? 'متاح' : 'غير متاح' }}</p><a class="qudrat-table-link" href="{{ route('admin.arabic-skills.questions.index', ['bank_id' => $bank->id, 'model_id' => $model->id]) }}">أسئلة {{ $model->title }}</a></div>
                        <form class="qudrat-form" method="POST" action="{{ route('admin.arabic-skills.models.update', $model) }}">@csrf @method('PATCH')<input type="hidden" name="model_form" value="{{ $model->id }}"><input type="hidden" name="revision" value="{{ $thisModelFailed ? old('revision') : $editor->revision($model) }}">@if($thisModelFailed && $errors->has('revision'))<p class="qudrat-note">راجع مدخلاتك، ثم انسخها وأعد تحميل الصفحة قبل الحفظ. العنوان الحالي: {{ $model->title }}.</p>@endif<label>عنوان {{ $model->title }}<input name="title" value="{{ $thisModelFailed ? old('title') : $model->title }}" maxlength="255" required></label><button class="qudrat-button qudrat-secondary">حفظ عنوان النموذج</button></form>
                    </div>
                @endforeach
            </details>
        </article>
    @endforeach
</section>
@endsection
