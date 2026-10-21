@extends('layouts.admin')
@section('content')
@php
    $readingId = $categories->firstWhere('slug', 'reading_comprehension')?->id;
    $selectedCategory = old('category_id', $question->category_id ?? $categories->first()?->id);
    $optionTexts = $question->options->pluck('text', 'local_key');
@endphp
<section data-qudrat-admin>
    @include('admin.qudrat._nav')
    <div class="qudrat-heading"><div><h2>{{ $question->exists ? 'تحرير السؤال' : 'إضافة سؤال' }}</h2><p>{{ $question->archived_at ? 'هذا السؤال مؤرشف. ألغِ أرشفته لإعادته إلى الأسئلة المنشورة.' : 'يُنشر السؤال تلقائيًا عند الحفظ. أكمل جميع الحقول وحدد الإجابة الصحيحة.' }}</p></div>
        @if($question->exists)<a class="qudrat-button qudrat-secondary" href="{{ route('admin.qudrat.questions.preview', $question) }}">معاينة السؤال</a>@endif
    </div>
    <form action="{{ $question->exists ? route('admin.qudrat.questions.update', $question) : route('admin.qudrat.questions.store') }}" method="POST" class="qudrat-form qudrat-editor">
        @csrf
        @if($question->exists) @method('PUT') @endif
        <input type="hidden" name="revision" value="{{ $revision }}">
        <div class="qudrat-grid">
            <label for="category_id">القسم<select name="category_id" id="category_id" required>
                @foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)$selectedCategory === (string)$category->id)>{{ $category->title }}</option>@endforeach
            </select></label>
            <label for="sort_order">الترتيب داخل القسم<input type="number" id="sort_order" name="sort_order" min="1" max="65535" value="{{ old('sort_order', $question->sort_order) }}" required></label>
        </div>
        <div id="qudrat-passage-field" @if((string)$selectedCategory !== (string)$readingId) hidden @endif>
            <label for="passage_id">النص القرائي المشترك<select id="passage_id" name="passage_id" @disabled((string)$selectedCategory !== (string)$readingId)>
                <option value="">اختر النص</option>
                @foreach($passages as $passage)<option value="{{ $passage->id }}" @selected((string)old('passage_id', $question->passage_id) === (string)$passage->id)>النص {{ $passage->sort_order }} — {{ Str::limit($passage->text, 65) }}</option>@endforeach
            </select></label>
            <p class="qudrat-note">النص مشترك بين عدة أسئلة. يُحرّر من صفحة النصوص القرائية.</p>
        </div>
        <label for="prompt">السؤال<textarea id="prompt" name="prompt" rows="3" maxlength="10000" required>{{ old('prompt', $question->prompt) }}</textarea></label>
        <fieldset><legend>الخيارات والإجابة الصحيحة</legend><p class="qudrat-note">أدخل أربعة خيارات، وحدد إجابة صحيحة واحدة. الخيار المكوّن من كلمتين يبقى خيارًا واحدًا.</p>
            @foreach(['a' => 'أ', 'b' => 'ب', 'c' => 'ج', 'd' => 'د'] as $key => $label)
                <div class="qudrat-option-edit">
                    <label class="qudrat-radio" for="correct-{{ $key }}"><input id="correct-{{ $key }}" type="radio" name="correct_option_key" value="{{ $key }}" @checked(old('correct_option_key', $question->correct_option_key) === $key) required aria-label="الخيار {{ $label }} هو الصحيح"><span>{{ $label }}</span></label>
                    <label class="qudrat-option-text" for="option-{{ $key }}"><span class="sr-only">نص الخيار {{ $label }}</span><input id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ old('options.'.$key, $optionTexts[$key] ?? '') }}" required maxlength="10000"></label>
                </div>
            @endforeach
        </fieldset>
        <div class="qudrat-grid">
            <label for="hint">التلميح<textarea id="hint" name="hint" rows="4" maxlength="10000" required>{{ old('hint', $question->hint) }}</textarea></label>
            <label for="explanation">تفسير الإجابة<textarea id="explanation" name="explanation" rows="4" maxlength="10000" required>{{ old('explanation', $question->explanation) }}</textarea></label>
        </div>
        <div class="qudrat-actions"><button class="qudrat-button" type="submit">حفظ السؤال</button><a href="{{ route('admin.qudrat.questions.index') }}">العودة إلى الأسئلة</a></div>
    </form>
    @if($question->exists)
        <div class="qudrat-management">
            <form action="{{ route('admin.qudrat.questions.archive', $question) }}" method="POST">@csrf @method('PATCH')
                <input type="hidden" name="revision" value="{{ $revision }}"><input type="hidden" name="archived" value="{{ $question->archived_at ? 0 : 1 }}">
                <button class="qudrat-button qudrat-secondary">{{ $question->archived_at ? 'إلغاء الأرشفة' : 'أرشفة السؤال' }}</button>
            </form>
            <p class="qudrat-note">الأرشفة تخفي السؤال وتحافظ على سجل التدريبات. لا يُحذف السؤال.</p>
        </div>
    @endif
</section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const category = document.getElementById('category_id');
    const field = document.getElementById('qudrat-passage-field');
    const passage = document.getElementById('passage_id');
    category.addEventListener('change', () => {
        const reading = category.value === @json((string)$readingId);
        field.hidden = !reading;
        passage.disabled = !reading;
        if (!reading) passage.value = '';
    });
});
</script>
@endpush
