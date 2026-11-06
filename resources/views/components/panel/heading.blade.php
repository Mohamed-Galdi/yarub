@props(['title', 'description' => null])

<header class="panel-heading">
    <div>
        <h1>{{ $title }}</h1>
        @if($description)
            <p>{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="panel-actions">{{ $actions }}</div>
    @endisset
</header>
