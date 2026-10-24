@extends('layouts.admin')
@section('content')
<section data-qudrat-admin data-arabic-admin dir="rtl">
    @include('admin.arabic-skills._nav')
    <div class="qudrat-heading"><div><h2>الأسئلة</h2><p>كل سؤال جديد يُنشر فور اكتمال الحفظ. الإضافات غير محدودة وتبقى خارج النماذج الأصلية.</p></div><a class="qudrat-button" href="{{ route('admin.arabic-skills.questions.create') }}">إضافة سؤال</a></div>
    <form class="qudrat-form arabic-admin-filters" method="GET">
        <label>البحث في السؤال أو المعرّف<input type="search" name="q" value="{{ request('q') }}" maxlength="255"></label>
        <label>البنك<select name="bank_id"><option value="">جميع البنوك</option>@foreach($banks as $bank)<option value="{{ $bank->id }}" @selected(request('bank_id') == $bank->id)>{{ $bank->title }}</option>@endforeach</select></label>
        <label>النموذج<select name="model_id"><option value="">جميع النماذج والإضافات</option>@foreach($models as $model)<option value="{{ $model->id }}" @selected(request('model_id') == $model->id)>{{ $model->bank->title }} — {{ $model->title }}</option>@endforeach</select></label>
        <label>نوع السؤال<select name="type"><option value="">جميع الأنواع</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>المهارة<select name="skill"><option value="">جميع المهارات</option>@foreach($skills as $skill)<option @selected(request('skill') === $skill)>{{ $skill }}</option>@endforeach</select></label>
        <label>حالة النشر<select name="active"><option value="">جميع الحالات</option><option value="1" @selected(request('active') === '1')>منشور</option><option value="0" @selected(request('active') === '0')>مؤرشف</option></select></label>
        <div class="qudrat-actions"><button class="qudrat-button">بحث</button><a href="{{ route('admin.arabic-skills.questions.index') }}">مسح المرشحات</a></div>
    </form>
    <p class="qudrat-note arabic-admin-total">{{ $questions->total() }} سؤال</p>
    @forelse($questions as $question)
        <article class="arabic-admin-row">
            <div><p class="qudrat-note">{{ $question->bank->title }} · {{ $question->sourceModel?->title ?? 'إضافة المشرف' }} · {{ $types[$question->type] }} · {{ $question->is_active ? 'منشور' : 'مؤرشف' }}</p><h3 class="qudrat-prose">{{ Str::limit($question->prompt, 220) }}</h3><p class="qudrat-note">{{ $question->skill }} · <bdi>{{ $question->source_id }}</bdi></p></div>
            <div class="qudrat-actions"><a class="qudrat-table-link" aria-label="تحرير {{ $question->source_id }}" href="{{ route('admin.arabic-skills.questions.edit', $question) }}">تحرير</a><a class="qudrat-table-link" aria-label="معاينة {{ $question->source_id }}" href="{{ route('admin.arabic-skills.questions.preview', $question) }}">معاينة</a></div>
        </article>
    @empty<p class="qudrat-notice">لا توجد أسئلة تطابق البحث. غيّر المرشحات أو أضف سؤالًا.</p>@endforelse
    {{ $questions->links() }}
</section>
@endsection
