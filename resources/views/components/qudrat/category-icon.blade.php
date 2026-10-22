@props(['type'])
<svg class="qudrat-category-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($type)
        @case('verbal_analogies')
            <path d="M4 7h13m-3-3 3 3-3 3M20 17H7m3-3-3 3 3 3" />
            @break
        @case('sentence_completion')
            <path d="M4 5h16M4 12h6m4 0h6M4 19h16M10 9v6m4-6v6" />
            @break
        @case('contextual_errors')
            <path d="M4 5h16M4 11h8M4 17h6m6-8 5 5m0-5-5 5m0 4h5" />
            @break
        @case('odd_word_out')
            <rect x="3" y="3" width="6" height="6" rx="1" /><rect x="15" y="3" width="6" height="6" rx="1" /><rect x="3" y="15" width="6" height="6" rx="1" /><path d="m18 14 4 4-4 4-4-4z" />
            @break
        @case('reading_comprehension')
            <path d="M12 5v15m0-15C9 3 5 3 2 4v15c3-1 7-1 10 1 3-2 7-2 10-1V4c-3-1-7-1-10 1ZM5 8h4m-4 4h4m6-4h4m-4 4h4" />
            @break
        @default
            <circle cx="7" cy="12" r="4" /><circle cx="17" cy="6" r="3" /><circle cx="17" cy="18" r="3" /><path d="m10 10 4-3m-4 7 4 3" />
    @endswitch
</svg>
