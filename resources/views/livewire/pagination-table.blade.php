<div wire:loading wire:target="gotoPage, previousPage, nextPage" class="loading-overlay">
</div>
@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
        <!-- Total Records and Current Page Range -->
        <div class="text-dark small">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} records
        </div>

        <!-- Pagination as -->
        <nav aria-label="Pagination">
            <ul class="pagination mb-0">
                <!-- Previous Page Link -->
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                        <span class="page-link" aria-hidden="true">&laquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a href="javascript:void(0)" wire:click="previousPage" class="page-link cursor-pointer" rel="prev" aria-label="Previous">&laquo;</a>
                    </li>
                @endif

                <!-- Pagination Elements -->
                @foreach ($elements as $element)
                    <!-- "Three Dots" Separator -->
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    <!-- Array Of Links -->
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item">
                                    <a href="javascript:void(0)" wire:click="gotoPage({{ $page }})" class="page-link cursor-pointer">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <!-- Next Page Link -->
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a href="#" wire:click="nextPage" class="page-link" rel="next" aria-label="Next">&raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                        <span class="page-link" aria-hidden="true">&raquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
