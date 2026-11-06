@props(['title', 'message'])

<div {{ $attributes->class(['panel-empty']) }}>
    <p class="panel-card-title">{{ $title }}</p>
    <p>{{ $message }}</p>
    {{ $slot }}
</div>
