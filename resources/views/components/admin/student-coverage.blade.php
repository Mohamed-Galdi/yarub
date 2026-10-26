@props(['metric', 'label'])
<div class="student-coverage">
    @if($metric['coverage'] === null)<span class="users-muted">غير متاح</span>
    @else
        <div class="student-coverage-values"><bdi dir="ltr">{{ $metric['answered_questions'] }} <span>/ {{ $metric['available_questions'] }}</span></bdi><span class="users-muted">{{ $metric['participated'] ? round($metric['coverage']).'٪' : 'لم يبدأ' }}</span></div>
        <progress value="{{ $metric['answered_questions'] }}" max="{{ max(1, $metric['available_questions']) }}" aria-label="{{ $label }}" title="عدد الأسئلة المختلفة التي أُجيبت من الأسئلة المتاحة حاليًا. قد تتغير التغطية عند إضافة المحتوى أو إخفائه.">{{ $metric['coverage'] }}٪</progress>
    @endif
</div>
