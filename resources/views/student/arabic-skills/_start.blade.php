@if($resume)
    <a class="qudrat-button" aria-label="متابعة التدريب — {{ $contextLabel }}" href="{{ route('student.arabic-skills.show', $resume['id']) }}">متابعة التدريب</a>
@elseif($available)
    <form method="POST" action="{{ route('student.arabic-skills.start', $bankId) }}">@csrf
        <input type="hidden" name="mode" value="{{ isset($modelId) ? 'model' : 'practice' }}">
        @isset($modelId)<input type="hidden" name="model_id" value="{{ $modelId }}">@endisset
        <button class="qudrat-button" aria-label="{{ isset($modelId) ? 'ابدأ النموذج' : 'ابدأ التدريب' }} — {{ $contextLabel }}">{{ isset($modelId) ? 'ابدأ النموذج' : 'ابدأ التدريب' }}</button>
    </form>
@elseif(!$bankActive)
    <p class="qudrat-note">البنك متوقف للتدريبات الجديدة.</p>
@else
    <p class="qudrat-note">{{ isset($modelId) ? 'النموذج غير متاح حتى تكتمل أسئلته الثلاثون.' : 'التدريب غير متاح حاليًا. يلزم توفر عشرة أسئلة نشطة.' }}</p>
@endif
