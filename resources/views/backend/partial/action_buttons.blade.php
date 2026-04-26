{{-- edit --}}
@if (auth()->guard('admin')->user()->can('product_edit'))
<a href="{{ route('admin.product.edit', $item->id) }}" class="btn btn-primary btn-sm btn-icon mr-2" data-toggle="tooltip"
    title="Edit">
    <i class="fas fa-pencil-alt"></i>
</a>
@endif

@if (auth()->guard('admin')->user()->can('product_delete'))
<button class="btn btn-danger btn-icon btn-sm deleteProduct" data-href="{{ route('admin.product.destroy', $item->id) }}"
    data-toggle="tooltip" title="Delete" type="button">
    <i class="fas fa-trash"></i>
</button>
@endif

<script>
    $(document).ready(function() {
        $('.deleteProduct').on('click', function() {
            const modal = $('#delete');

            modal.find('form').attr('action', $(this).data('href'));

            modal.modal('show');
        });
    });
</script>
