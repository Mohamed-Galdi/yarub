@props(['value', 'relative' => false, 'empty' => 'لم يبدأ'])
@if($value)
    @php($time = \Carbon\Carbon::parse($value))
    <time datetime="{{ $time->toIso8601String() }}" title="{{ $time->format('Y-m-d H:i:s') }} UTC">@if($relative){{ $time->diffForHumans() }}@else<bdi dir="ltr">{{ $time->format('d-m-Y') }}</bdi>@endif</time>
@else<span class="users-muted">{{ $empty }}</span>@endif
