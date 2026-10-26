@php
    $response = is_array($item['response']) ? $item['response'] : [];
@endphp
<h3 class="student-review-prompt">{{ $question['prompt'] }}</h3>
@if($question['type'] === 'matching')
    <ul class="student-matching-options" aria-label="خيارات المطابقة">@foreach($question['options'] as $option)<li><strong>{{ $option['label'] }}</strong><span class="student-review-prose">{{ $option['text'] }}</span></li>@endforeach</ul>
    <dl class="student-matching-review">
        @foreach($question['matching_items'] as $match)
            @php
                $selectedId = collect($response['matches'] ?? [])->firstWhere('item_id', $match['id'])['option_id'] ?? null;
                $correctId = collect($question['answer']['matches'])->firstWhere('item_id', $match['id'])['option_id'] ?? null;
            @endphp
            <div><dt class="student-review-prose">{{ $match['text'] }}</dt><dd><span>اختيار الطالب: </span><span class="student-review-prose">{{ $item['submitted_at'] ? (collect($question['options'])->firstWhere('id', $selectedId)['text'] ?? 'غير متاح') : 'لم تُرسل إجابة' }}</span></dd><dd><span>الإجابة الصحيحة: </span><span class="student-review-prose">{{ collect($question['options'])->firstWhere('id', $correctId)['text'] ?? 'غير متاح' }}</span></dd></div>
        @endforeach
    </dl>
@else
    <ul class="student-readonly-options" aria-label="الخيارات المحفوظة">
        @foreach($question['options'] as $option)<li><span class="student-review-prose">{{ $option['text'] }}</span><span class="student-option-marks">@if(($response['option_id'] ?? null) === $option['id'] && $item['submitted_at'])<span>اختيار الطالب</span>@endif @if(($question['answer']['option_id'] ?? null) === $option['id'])<span class="student-correct-label">الإجابة الصحيحة</span>@endif</span></li>@endforeach
    </ul>
@endif
@if($item['submitted_at'] && $item['earned_points'] !== null)<p class="student-method-note">النقاط المحفوظة: <bdi dir="ltr">{{ $item['earned_points'] }} / {{ $question['max_score'] }}</bdi></p>@endif
