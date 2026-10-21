@props(['correctKey', 'explanation'])
<div class="qudrat-feedback"><p>الإجابة الصحيحة: <strong>{{ ['a' => 'أ', 'b' => 'ب', 'c' => 'ج', 'd' => 'د'][$correctKey] ?? 'غير محددة' }}</strong></p><p class="qudrat-prose">{{ $explanation ?: 'لم يُضف تفسير بعد.' }}</p></div>
