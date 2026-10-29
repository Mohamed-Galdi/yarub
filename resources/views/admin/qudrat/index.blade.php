@extends('layouts.admin')
@section('content')
<section data-qudrat-admin>
    @include('admin.qudrat._nav')
    <div class="qudrat-heading"><div><h2>مراجعة محتوى القدرات</h2><p>تُنشر الأسئلة تلقائيًا عند إضافتها. يمكنك تحريرها أو أرشفتها من بنك الأسئلة.</p></div>
        <a class="qudrat-button" href="{{ route('admin.qudrat.questions.index') }}">مراجعة الأسئلة</a>
    </div>
    <div class="training-type-grid">
    @forelse($categories as $category)
        <article class="training-type-card">
            <div class="training-type-heading"><x-qudrat.category-icon :type="$category->slug" /><span class="training-type-status">{{ $category->is_published ? 'متاح' : 'متوقف' }}</span></div>
            <h3>{{ $category->title }}</h3>
            <dl class="training-type-counts"><div><dt>الأسئلة</dt><dd>{{ $category->total_count }}</dd></div><div><dt>منشور</dt><dd>{{ $category->published_count }}</dd></div><div><dt>مؤرشف</dt><dd>{{ $category->archived_count }}</dd></div></dl>
            <div class="training-type-actions"><a class="qudrat-button" href="{{ route('admin.qudrat.questions.index', ['category_id' => $category->id]) }}" aria-label="عرض الأسئلة — {{ $category->title }}">عرض الأسئلة</a><a class="practice-set-review" href="{{ route('admin.qudrat.questions.create', ['category_id' => $category->id]) }}" aria-label="إضافة سؤال — {{ $category->title }}">إضافة سؤال</a></div>
            <details class="qudrat-category-settings"><summary>إعدادات القسم</summary>
                <form action="{{ route('admin.qudrat.categories.update', $category) }}" method="POST" class="qudrat-form">
                    @csrf @method('PATCH')
                    <label for="title-{{ $category->id }}">اسم القسم<input id="title-{{ $category->id }}" name="title" value="{{ $category->title }}" required maxlength="255"></label>
                    <label for="order-{{ $category->id }}">الترتيب<input id="order-{{ $category->id }}" name="sort_order" type="number" min="1" max="65535" value="{{ $category->sort_order }}" required></label>
                    <button class="qudrat-button" type="submit">حفظ إعدادات القسم</button>
                </form>
            </details>
        </article>
    @empty
        <p class="qudrat-empty">لا توجد أقسام بعد. استورد المحتوى في بيئة التطوير أولًا.</p>
    @endforelse
    </div>
</section>
@endsection
