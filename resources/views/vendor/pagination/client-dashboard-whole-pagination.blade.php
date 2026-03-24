@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination">
  <ul class="pagination">

    {{-- Previous --}}
    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
      @if($paginator->onFirstPage())
        <span class="page-link" aria-disabled="true">‹</span>
      @else
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
      @endif
    </li>

    {{-- Page numbers --}}
    @foreach($elements as $element)
      @if(is_string($element))
        <li class="page-item disabled"><span class="page-link">…</span></li>
      @endif
      @if(is_array($element))
        @foreach($element as $page => $url)
          <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
          </li>
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
      @if($paginator->hasMorePages())
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
      @else
        <span class="page-link" aria-disabled="true">›</span>
      @endif
    </li>

  </ul>
</nav>
@endif