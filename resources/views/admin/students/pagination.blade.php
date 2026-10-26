@if($paginator->hasPages())
<nav class="users-pagination" aria-label="صفحات الطلاب">
    <button type="button" wire:click="previousPage" @disabled($paginator->onFirstPage()) aria-label="الصفحة السابقة">السابق</button>
    @foreach($elements as $element)
        @if(is_string($element))<span aria-hidden="true">{{ $element }}</span>@else
            @foreach($element as $page => $url)<button type="button" wire:click="gotoPage({{ $page }})" @if($page === $paginator->currentPage()) aria-current="page" @endif aria-label="الصفحة {{ $page }}">{{ $page }}</button>@endforeach
        @endif
    @endforeach
    <button type="button" wire:click="nextPage" @disabled(!$paginator->hasMorePages()) aria-label="الصفحة التالية">التالي</button>
</nav>
@endif
