@extends('layouts.admin')
@section('content')
<section data-qudrat-admin>
    @include('admin.qudrat._nav')
    <div class="qudrat-heading"><div><h2>النصوص القرائية المشتركة</h2><p>تحرير النص هنا يحدّث جميع معاينات الأسئلة المرتبطة به.</p></div></div>
    <div class="qudrat-passage-list">
        @forelse($passages as $passage)
            <article><div><h3><a href="{{ route('admin.qudrat.passages.edit', $passage) }}">النص {{ $passage->sort_order }}</a></h3><p>{{ Str::limit($passage->text, 160) }}</p><span class="qudrat-note">{{ $passage->questions_count }} أسئلة مرتبطة</span></div><a class="qudrat-table-link" href="{{ route('admin.qudrat.passages.edit', $passage) }}">تحرير النص</a></article>
        @empty<p class="qudrat-empty">لا توجد نصوص قرائية بعد.</p>@endforelse
    </div>
    <div class="qudrat-pagination">{{ $passages->links() }}</div>
</section>
@endsection
