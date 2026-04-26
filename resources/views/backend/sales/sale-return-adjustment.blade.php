@extends('backend.layout.master')
@push('style')
    <style>
        .selectric {
            border-radius: 4px !important;
        }
    </style>
@endpush
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active">
                        <a href="{{ route('admin.product.index') }}">{{ __('Products') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="return-form">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="invoice">Invoice No</label>
                                        <input type="text" id="invoice" value="{{ $sale->invoice_no }}" name="invoice"
                                            class="form-control" placeholder="Enter invoice no">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="type" class="form-label">Adjustment Type</label>
                                        <select name="type" class="form-control" disabled>
                                            <option value="sale_return" disabled selected>Return</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Reason (optional)</label>
                                        <textarea name="reason" rows="1" class="form-control" placeholder="e.g. Damaged, excess delivery"></textarea>
                                    </div>
                                </div>

                                <div id="product-table-wrapper">
                                    <table class="table table-bordered" id="product-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Sold Qty</th>
                                                <th>Available Return</th>
                                                <th>Return Qty</th>
                                                <th>Unit Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($sale->salesProduct as $item)
                                                <tr>
                                                    <td>{{ $item->product->name ?? '' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $item->quantity - $item->returned_quantity ?? 0 }}</td>
                                                    <td>
                                                        <input type="number" class="form-control return-qty"
                                                            data-price="{{ $item->unit_price }}"
                                                            data-product="{{ $item->product_id }}"
                                                            data-warehouse="{{ $item->warehouse_id }}"
                                                            max="{{ $item->quantity - $item->returned_quantity }}"
                                                            min="0"
                                                            value="{{ max($item->quantity - $item->returned_quantity, 0) }}"
                                                            {{ $item->quantity - $item->returned_quantity === 0 ? 'disabled' : '' }} />
                                                    </td>
                                                    <td>{{ $item->unit_price }} {{ $general->site_currency }}</td>
                                                    <td class="subtotal">{{ $item->unit_price * $item->quantity }}
                                                        {{ $general->site_currency }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No products found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <div class="text-start my-4">
                                        <h6>Total Return: <span
                                                id="total-return-amount">{{ $sale->salesProduct->sum('total') }}
                                                {{ $general->site_currency }}</span></h6>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">Submit Return</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Confirm Return Modal -->
        <div class="modal fade" id="confirmReturnModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirm Return</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to process this return?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Close</button>
                        <button type="button" class="btn btn-success" id="confirmReturnSubmit">Yes, Proceed</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $(document).on('input', '.return-qty', function() {
                let $input = $(this);
                let qty = parseFloat($input.val());
                let price = parseFloat($input.data('price'));
                let maxQty = parseInt($input.attr('max'));

                if (isNaN(qty) || qty < 1 || qty > maxQty) {
                    $input.addClass('is-invalid');
                    $input.closest('tr').find('.subtotal').text('0.00');
                } else {
                    $input.removeClass('is-invalid');
                    let subtotal = qty * price;
                    $input.closest('tr').find('.subtotal').text(subtotal.toFixed(2));
                }

                let total = 0;
                $('.return-qty').each(function() {
                    let val = parseFloat($(this).val());
                    let price = parseFloat($(this).data('price'));
                    let max = parseInt($(this).attr('max'));

                    if (!isNaN(val) && val >= 1 && val <= max) {
                        total += val * price;
                    }
                });

                $('#total-return-amount').text(total.toFixed(2) + ' {{ $general->site_currency }}');
            });

            let formData = null; // Keep in outer scope to access inside modal

            $('#return-form').on('submit', function(e) {
                e.preventDefault();

                const invoice = $('#invoice').val();
                if (!invoice) {
                    $('#invoice').addClass('is-invalid').css('border-color', 'red');
                    showToast('Please enter an invoice number', 'error');
                    return;
                }

                formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('invoice', invoice);
                formData.append('reason', $('[name="reason"]').val());

                let productIndex = 0;
                $('#product-table tbody tr').each(function() {
                    const $row = $(this);
                    const $returnQty = $row.find('.return-qty');
                    if (!$returnQty.length) return;

                    const productId = $returnQty.data('product');
                    const warehouse = $returnQty.data('warehouse');
                    const qty = parseFloat($returnQty.val());

                    if (!isNaN(qty) && qty > 0 && productId && warehouse) {
                        formData.append(`products[${productIndex}][id]`, productId);
                        formData.append(`products[${productIndex}][warehouse]`, warehouse);
                        formData.append(`products[${productIndex}][qty]`, qty);
                        productIndex++;
                    }
                });

                if (productIndex === 0) {
                    showToast('No valid return items selected.', 'error');
                    return;
                }

                // Show confirmation modal
                const modal = $('#confirmReturnModal');
                modal.modal('show');
            });

            // Handle actual submission when confirmed
            $('#confirmReturnSubmit').on('click', function() {
                const $submitBtn = $('#return-form').find('button[type="submit"]');
                const originalBtnText = $submitBtn.text();

                $submitBtn.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                );

                $('#return-form :input').prop('disabled', true);

                $.ajax({
                    url: '/sale-return-adjustment-store',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#invoice').removeClass('is-invalid').css('border-color', '');
                        window.location.href = "{{ route('admin.returnList.index') }}";
                    },
                    error: function(err) {
                        showToast('Return failed. Please try again.', 'error');
                        $('#return-form :input').prop('disabled', false);
                        $submitBtn.html(originalBtnText);
                    }
                });

                $('#confirmReturnModal').modal('hide');
            });
        });
    </script>
@endpush
