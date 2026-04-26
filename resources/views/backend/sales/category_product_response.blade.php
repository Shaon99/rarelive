<div class="scrolling-pagination" id="searchable-product">
    <div class="row">
        @forelse ($products as $item)
            <div class="col-md-4 col-lg-4 col-sm-6 col-12" id="product-img" data-product="{{ $item->id }}">
                <div class="card p-1 rounded border-product">
                    <center>
                        @if ($item->image)
                            <img src="{{ getFile('product', $item->image) }}" alt="img" class="img-fluid"
                                width="80px" height="80px">
                        @else
                            <img src="{{ getFile('default', $general->default_image) }}" alt="img"
                                class="img-fluid" width="100px" height="100px">
                        @endif
                        <small class="mb-0">{{ $item->name }} ({{ $item->code }})</small>
                    </center>
                </div>
            </div>
        @empty
            <div class="col-md-12 mt-5">
                <p class="text-center mt-5 mb-5">No records has been added yet. </p>
            </div>
        @endforelse
        {{ @$products->links('backend.partial.paginate') }}
    </div>
</div>
<script>
    $('ul.pagination').hide();
    $(function() {
        $('.scrolling-pagination').jscroll({
            autoTrigger: true,
            padding: 0,
            nextSelector: '.pagination li.active + li a',
            contentSelector: 'div.scrolling-pagination',
            callback: function() {
                $('ul.pagination').remove();
            }
        });
    });
</script>
