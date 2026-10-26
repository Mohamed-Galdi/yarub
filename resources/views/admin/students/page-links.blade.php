<div class="student-pagination">
    <span>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} من {{ $paginator->total() }}</span>
    @if($paginator->hasPages())
    <nav aria-label="صفحات السجل">
        @if($paginator->onFirstPage())<span aria-disabled="true">السابق</span>@else<a href="{{ $paginator->previousPageUrl() }}">السابق</a>@endif
        @foreach($elements as $element)
            @if(is_string($element))<span aria-hidden="true">{{ $element }}</span>@else
                @foreach($element as $page => $url)<a href="{{ $url }}" @if($page === $paginator->currentPage()) aria-current="page" @endif aria-label="الصفحة {{ $page }}">{{ $page }}</a>@endforeach
            @endif
        @endforeach
        @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">التالي</a>@else<span aria-disabled="true">التالي</span>@endif
    </nav>
    @endif
</div>
