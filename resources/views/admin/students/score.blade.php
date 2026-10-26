@if($score['valid'])
    <bdi dir="ltr">{{ $score['earned'] }} / {{ $score['out_of'] }}</bdi><span class="student-cell-note">{{ $score['percentage'] }}٪ · {{ $score['kind'] === 'points' ? 'نقاط' : 'إجابات صحيحة' }}{{ $score['final'] ? '' : ' · غير نهائية' }}</span>
@else<span class="users-muted">غير متاح</span>@endif
