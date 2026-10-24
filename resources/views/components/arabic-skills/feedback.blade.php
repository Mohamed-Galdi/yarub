@props(['question', 'response', 'feedback', 'review' => false])
<section class="qudrat-result-feedback {{ $feedback['result'] === 'correct' ? 'qudrat-correct' : 'qudrat-incorrect' }}" tabindex="-1" data-arabic-feedback aria-label="نتيجة الإجابة">
    @php
        $feedbackTitle = ['correct' => 'إجابة صحيحة', 'partial' => 'إجابة صحيحة جزئيًا', 'incorrect' => 'إجابة غير صحيحة'][$feedback['result']];
    @endphp
    @if($review)
        <h4 class="arabic-feedback-title">{{ $feedbackTitle }} · <bdi dir="ltr">{{ $feedback['earned_points'] }} / {{ $feedback['max_score'] }}</bdi></h4>
    @else
        <h2 class="arabic-feedback-title">{{ $feedbackTitle }} · <bdi dir="ltr">{{ $feedback['earned_points'] }} / {{ $feedback['max_score'] }}</bdi></h2>
    @endif
    @if($question['type'] === 'matching')
        <dl class="arabic-match-review">
            @foreach($question['matching_items'] as $item)
                @php
                    $chosen = collect($response['matches'])->firstWhere('item_id', $item['id'])['option_id'];
                    $correct = collect($feedback['answer']['matches'])->firstWhere('item_id', $item['id'])['option_id'];
                @endphp
                <div><dt class="qudrat-prose">{{ $item['text'] }}</dt><dd>اختيارك: <span class="qudrat-prose">{{ collect($question['options'])->firstWhere('id', $chosen)['text'] }}</span> · {{ $chosen === $correct ? 'صحيح' : 'غير صحيح' }}
                    @if($chosen !== $correct)<br>الصحيح: <span class="qudrat-prose">{{ collect($question['options'])->firstWhere('id', $correct)['text'] }}</span>@endif
                </dd></div>
            @endforeach
        </dl>
    @else
        <p class="qudrat-prose">الإجابة الصحيحة: {{ collect($question['options'])->firstWhere('id', $feedback['answer']['option_id'])['text'] }}</p>
    @endif
    <p class="qudrat-prose arabic-explanation">{{ $feedback['explanation'] }}</p>
</section>
