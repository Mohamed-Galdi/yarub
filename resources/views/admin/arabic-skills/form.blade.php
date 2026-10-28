@extends('layouts.admin')
@section('content')
@php
    $type = old('type', $question->type);
    $optionTexts = collect($question->options ?? [])->pluck('text', 'id');
    $itemTexts = collect($question->matching_items ?? [])->pluck('text', 'id');
    $matches = collect($question->answer['matches'] ?? [])->pluck('option_id', 'item_id');
    $optionLabels = $question->exists ? collect($question->options)->pluck('label', 'id')->all() : ($type === 'true_false' ? ['true' => 'صح', 'false' => 'خطأ'] : ($type === 'matching' ? ['a' => 'أ', 'b' => 'ب'] : ['a' => 'أ', 'b' => 'ب', 'c' => 'ج']));
    $itemLabels = array_slice(['item_1' => 'الأولى', 'item_2' => 'الثانية', 'item_3' => 'الثالثة', 'item_4' => 'الرابعة'], 0, $question->exists && $type === 'matching' ? count($question->matching_items) : 2);
@endphp
<section data-qudrat-admin data-arabic-admin dir="rtl">
    @include('admin.arabic-skills._nav')
    <div class="qudrat-heading"><div><h2>{{ $question->exists ? 'تحرير السؤال' : 'إضافة سؤال' }}</h2><p>{{ $question->exists && !$question->is_active ? 'السؤال مؤرشف. الحفظ يحافظ على أرشفته؛ استخدم إعادة النشر لإتاحته.' : 'يُنشر السؤال مباشرةً عند الحفظ. أكمل المحتوى وحدد الإجابة الصحيحة.' }}</p></div>@if($question->exists)<a class="qudrat-button qudrat-secondary" href="{{ route('admin.arabic-skills.questions.preview', $question) }}">معاينة السؤال</a>@endif</div>
    @if(!$question->exists)
        <form class="qudrat-form arabic-admin-type" method="GET"><input type="hidden" name="bank_id" value="{{ $question->bank_id }}"><label>نوع السؤال الجديد<select name="type">@foreach($types as $value => $label)<option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>@endforeach</select></label><button class="qudrat-button qudrat-secondary">فتح محرر النوع</button></form>
    @endif
    <form class="qudrat-form qudrat-editor" method="POST" action="{{ $question->exists ? route('admin.arabic-skills.questions.update', $question) : route('admin.arabic-skills.questions.store') }}">
        @csrf @if($question->exists) @method('PUT') @endif
        <input type="hidden" name="revision" value="{{ old('revision', $revision) }}"><input type="hidden" name="type" value="{{ $type }}">
        <div class="qudrat-grid"><label>البنك<select name="bank_id" required @disabled($question->exists)>@foreach($banks as $bank)<option value="{{ $bank->id }}" @selected((string)old('bank_id', $question->bank_id) === (string)$bank->id)>{{ $bank->title }}</option>@endforeach</select></label><label>المهارة<input name="skill" value="{{ old('skill', $question->skill) }}" maxlength="255" required></label></div>
        @if($question->exists)<input type="hidden" name="bank_id" value="{{ $question->bank_id }}">@endif
        <p class="qudrat-note">{{ $types[$type] }} · {{ ($question->max_score ?? 2) === 4 ? 'أربع درجات' : 'نقطتان' }} للسؤال@if($type === 'matching')، نقطة لكل مطابقة صحيحة. يمكن تكرار اختيار الإجابة نفسها.@endif</p>
        <label>نص السؤال<textarea name="prompt" rows="5" maxlength="20000" required>{{ old('prompt', $question->prompt) }}</textarea></label>
        <fieldset><legend>الخيارات والإجابة الصحيحة</legend>
            @foreach($optionLabels as $id => $label)
                <div class="qudrat-option-edit">
                    @if($type !== 'matching')<label class="qudrat-radio"><input type="radio" name="correct" value="{{ $id }}" @checked(old('correct', $question->answer['option_id'] ?? '') === $id) required><span>{{ $label }}{{ $type !== 'true_false' ? ' — الصحيح' : '' }}</span></label>@endif
                    @if($type !== 'true_false')<label class="qudrat-option-text">نص الخيار {{ $label }}<textarea rows="2" name="options[{{ $id }}]" maxlength="20000" required>{{ old('options.'.$id, $optionTexts[$id] ?? '') }}</textarea></label>@endif
                </div>
            @endforeach
        </fieldset>
        @if($type === 'matching')
            <fieldset><legend>عبارات المطابقة</legend>
                @foreach($itemLabels as $id => $label)
                    <div class="qudrat-grid arabic-admin-match"><label>العبارة {{ $label }}<textarea name="items[{{ $id }}]" maxlength="20000" required>{{ old('items.'.$id, $itemTexts[$id] ?? '') }}</textarea></label><label>الإجابة الصحيحة للعبارة {{ $label }}<select name="matches[{{ $id }}]" required><option value="">اختر الإجابة</option>@foreach($optionLabels as $value => $text)<option value="{{ $value }}" @selected(old('matches.'.$id, $matches[$id] ?? '') === $value)>الخيار {{ $text }}</option>@endforeach</select></label></div>
                @endforeach
            </fieldset>
        @endif
        <label>تفسير الإجابة<textarea name="explanation" rows="5" maxlength="20000" required>{{ old('explanation', $question->explanation) }}</textarea></label>
        <label>تلميح اختياري<textarea name="hint" rows="3" maxlength="20000" aria-describedby="hint-note">{{ old('hint', $question->hint) }}</textarea></label><p id="hint-note" class="qudrat-note">اتركه فارغًا لإخفاء زر التلميح. يظهر التفسير بعد إرسال الإجابة فقط.</p>
        <div class="qudrat-actions"><button class="qudrat-button">{{ $question->exists ? 'حفظ السؤال' : 'حفظ ونشر السؤال' }}</button><a href="{{ route('admin.arabic-skills.questions.index') }}">العودة إلى الأسئلة</a></div>
    </form>
    @if($question->exists)
        <div class="qudrat-management"><form method="POST" action="{{ route('admin.arabic-skills.questions.archive', $question) }}">@csrf @method('PATCH')<input type="hidden" name="revision" value="{{ $revision }}"><input type="hidden" name="archived" value="{{ $question->is_active ? 1 : 0 }}"><button class="qudrat-button qudrat-secondary">{{ $question->is_active ? 'أرشفة السؤال' : 'إعادة نشر السؤال' }}</button></form><p class="qudrat-note">لا يُحذف السؤال. تحتفظ التدريبات السابقة والجارية بالمحتوى الأصلي.</p></div>
    @endif
</section>
@endsection
