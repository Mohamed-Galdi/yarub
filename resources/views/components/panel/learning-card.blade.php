@props([
    'title',
    'href',
    'kind' => 'course',
    'type' => null,
    'description' => null,
    'headingLevel' => 2,
])

<article {{ $attributes->class(['panel-learning-card']) }}>
    <div class="panel-learning-card__body">
        <div class="panel-learning-card__meta">
            <span class="panel-learning-card__type">
                @if($kind === 'lesson')
                    <x-icons.lesson aria-hidden="true" />
                @else
                    <x-icons.course aria-hidden="true" />
                @endif
                <span dir="auto">{{ $type ?: ($kind === 'lesson' ? 'شرح' : 'دورة') }}</span>
            </span>
            @isset($status)
                {{ $status }}
            @endisset
        </div>

        <div class="panel-learning-card__intro">
            <h{{ $headingLevel === 3 ? 3 : 2 }} class="panel-learning-card__title" dir="auto">
                <a href="{{ $href }}">{{ $title }}</a>
            </h{{ $headingLevel === 3 ? 3 : 2 }}>
            @if(filled($description))
                <p class="panel-learning-card__description" dir="auto">{{ Str::limit(trim(strip_tags($description)), 160) }}</p>
            @endif
        </div>

        <dl class="panel-learning-card__details">
            {{ $details }}
        </dl>

        @isset($pricing)
            <div class="panel-learning-card__pricing">
                {{ $pricing }}
            </div>
        @endisset
    </div>

    <footer class="panel-learning-card__actions">
        {{ $slot }}
    </footer>
</article>
