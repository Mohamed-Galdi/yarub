@props(['label', 'value', 'unit' => null])

<x-panel.card class="panel-metric">
    <dt>{{ $label }}</dt>
    <dd>
        <bdi>{{ $value }}</bdi>
        @if($unit)
            <span>{{ $unit }}</span>
        @endif
    </dd>
</x-panel.card>
