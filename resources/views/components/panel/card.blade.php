@props(['title' => null])

<div {{ $attributes->class(['panel-surface']) }}>
    @if($title)
        <h3 class="panel-card-title">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
