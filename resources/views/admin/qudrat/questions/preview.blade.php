@extends('layouts.admin')
@section('content')
<section data-qudrat-admin>
    @include('admin.qudrat._nav')
    <div class="qudrat-heading"><div><h2>معاينة السؤال</h2><p>عرض للمشرف فقط، ولا يسجّل إجابة أو تقدّمًا للطالب.</p></div><a class="qudrat-button qudrat-secondary" href="{{ route('admin.qudrat.questions.edit', $question) }}">تحرير السؤال</a></div>
    <x-qudrat.player :prompt="$question->prompt" :options="$question->options->map(fn($option) => ['key' => $option->local_key, 'text' => $option->text])->all()" :category="$question->category->slug" :title="$question->category->title" :passage="$question->passage?->text" />
    <div class="qudrat-preview-notes">
        <details><summary>عرض التلميح</summary><p class="qudrat-prose">{{ $question->hint ?: 'لم يُضف تلميح بعد.' }}</p></details>
        <details><summary>عرض الإجابة والتفسير للمراجعة</summary>
            <x-qudrat.feedback :correct-key="$question->correct_option_key" :explanation="$question->explanation" />
        </details>
    </div>
</section>
@endsection
