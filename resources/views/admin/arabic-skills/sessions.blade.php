@extends('layouts.admin')
@section('content')
<section data-qudrat-admin data-arabic-admin dir="rtl">
    @include('admin.arabic-skills._nav')
    <div class="qudrat-heading"><div><h2>تدريبات الطلاب</h2><p>راجع الإجابات المسلّمة ودرجاتها من نسخة المحتوى وقت بدء التدريب.</p></div></div>
    <form class="qudrat-form arabic-admin-filters" method="GET">
        <label>اسم الطالب أو بريده<input type="search" name="student" value="{{ request('student') }}" maxlength="255"></label>
        <label>البنك<select name="bank_id"><option value="">جميع البنوك</option>@foreach($banks as $bank)<option value="{{ $bank->id }}" @selected(request('bank_id') == $bank->id)>{{ $bank->title }}</option>@endforeach</select></label>
        <label>النموذج<select name="model_id"><option value="">جميع النماذج</option>@foreach($models as $model)<option value="{{ $model->id }}" @selected(request('model_id') == $model->id)>{{ $model->bank->title }} — {{ $model->title }}</option>@endforeach</select></label>
        <label>نوع التدريب<select name="mode"><option value="">الكل</option><option value="practice" @selected(request('mode') === 'practice')>تدريب سريع</option><option value="model" @selected(request('mode') === 'model')>نموذج</option></select></label>
        <label>الحالة<select name="status"><option value="">جميع الحالات</option>@foreach(['active' => 'جارٍ', 'completed' => 'مكتمل', 'abandoned' => 'متروك دون إكمال'] as $value => $text)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $text }}</option>@endforeach</select></label>
        <label>بدأ من<input type="date" name="from" value="{{ request('from') }}"></label><label>بدأ حتى<input type="date" name="to" value="{{ request('to') }}"></label>
        <div class="qudrat-actions"><button class="qudrat-button">بحث</button><a href="{{ route('admin.arabic-skills.sessions.index') }}">مسح المرشحات</a></div>
    </form>
    <p class="qudrat-note arabic-admin-total">{{ $sessions->total() }} تدريب</p>
    @forelse($sessions as $session)
        <article class="arabic-admin-row"><div><h3>{{ $session->user?->name ?? 'طالب محذوف' }}</h3><p>{{ $session->bank->title }} · {{ $session->practice_set_title ?? $session->sourceModel?->title ?? 'تدريب سريع' }} · {{ ['active' => 'جارٍ', 'completed' => 'مكتمل', 'abandoned' => 'متروك دون إكمال'][$session->status] }}</p><p class="qudrat-note"><bdi>{{ $session->started_at->format('Y-m-d H:i') }}</bdi> · {{ $session->submitted_count }} من {{ $session->selected_question_count }} إجابة · <bdi dir="ltr">{{ $session->earned_points }} / {{ $session->available_points }}</bdi> نقطة{{ $session->status !== 'completed' ? ' (نتيجة غير نهائية)' : '' }}</p></div><a class="qudrat-table-link" href="{{ route('admin.arabic-skills.sessions.show', $session) }}" aria-label="مراجعة التدريب {{ $session->id }}">مراجعة التدريب</a></article>
    @empty<p class="qudrat-notice">لا توجد تدريبات تطابق البحث.</p>@endforelse
    {{ $sessions->links() }}
</section>
@endsection
