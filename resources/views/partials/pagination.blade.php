@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 text-sm" role="navigation">
        <p class="text-xs text-slate-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn-ghost btn-sm cursor-default opacity-40">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-ghost btn-sm">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-slate-400">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn-sm btn bg-primary-600 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn-ghost btn-sm">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-ghost btn-sm">›</a>
            @else
                <span class="btn-ghost btn-sm cursor-default opacity-40">›</span>
            @endif
        </div>
    </nav>
@endif
