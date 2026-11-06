@props(['route', 'item', 'title'])

@php($isActive = request()->routeIs("student.$item.*") || ($item === 'courses' && request()->routeIs('student.dashboard')))

<a data-student-nav-item href="{{ route($route) }}" @if($isActive) aria-current="page" @endif>
    <span aria-hidden="true">{{ $slot }}</span>
    {{ $title }}
</a>
