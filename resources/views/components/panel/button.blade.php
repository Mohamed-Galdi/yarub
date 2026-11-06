@props(['href' => null, 'variant' => 'secondary', 'type' => 'button'])

@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['panel-button', 'panel-button--'.$variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['panel-button', 'panel-button--'.$variant]) }}>{{ $slot }}</button>
@endif
