@extends('backend.layout.master')


@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>
                    @if (request()->has('duepurchases'))
                        {{ __('Due - ') }}
                    @endif {{ __($pageTitle) }}
                </h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        @if (request()->has('duepurchases'))
                            {{ __('Due - ') }}
                        @endif {{ __($pageTitle) }}
                    </div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.purchases.index') }}">{{ __('Purchases') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 border-r">
                                    <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
                                    <p><strong>Reference:</strong> {{ $purchase->reference_no ?? 'N/A' }}</p>
                                    <p><strong>Invoice:</strong> {{ $purchase->invoice_no ?? 'N/A' }}</p>
                                    <p><strong>Total Amount:</strong> {{ number_format($purchase->grand_total, 2) }}
                                        {{ $general->site_currency }}</p>
                                    <p><strong>Purchase Date:</strong> {{ $purchase->purchase_date }}</p>
                                    <p><strong>Created Date:</strong> {{ $purchase->created_at->format('Y-m-d H:i A') }}
                                    </p>
                                </div>
                                <div class="col-md-9">
                                    <!-- Form to submit the return -->
                                    <form action="{{ route('admin.purchase_returns.store', $purchase->id) }}"
                                        method="POST" class="needs-validation" novalidate="">
                                        @csrf
                                        <div class="row">
                                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                                            <input type="hidden" name="warehouse_id"
                                                value="{{ $purchase->warehouse_id }}">
                                            <div class="col-md-12">
                                                <h6>Returned Products</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('#') }}</th>
                                                                <th>{{ __('Product Name') }}</th>
                                                                <th>{{ __('Purchased Quantity') }}</th>
                                                                <th>{{ __('Returned Quantity') }}</th>
                                                                <th>{{ __('Remaining Quantity') }}</th>
                                                                <th>{{ __('Return Quantity') }}</th>
                                                                <th>{{ __('Unit Price') }}</th>
                                                                <th>{{ __('Total Price') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="product-list">
                                                            <p class="error-text text-danger text-center"></p>

                                                            <!-- Loop through each purchased product -->
                                                            @foreach ($purchase->purchasesProduct as $purchaseProduct)
                                                                @php
                                                                    $purchasedQty = $purchaseProduct->quantity;
                                                                    $returnedQty =
                                                                        $returnedQuantities[
                                                                            $purchaseProduct->product_id
                                                                        ] ?? 0;
                                                                    $remainingQty = $purchasedQty - $returnedQty;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $purchaseProduct->product->name }}
                                                                        ({{ $purchaseProduct->product->code ?? 'sku' }})
                                                                    </td>
                                                                    <td>{{ number_format($purchasedQty, 0) }}</td>
                                                                    <!-- Show purchased quantity -->
                                                                    <td>{{ number_format($returnedQty, 0) }}</td>
                                                                    <!-- Show returned quantity -->
                                                                    <td><span id="remainQty">{{ number_format($remainingQty, 0) }}</span></td>
                                                                    <!-- Show remaining quantity -->

                                                                    <td>
                                                                        <input type="hidden"
                                                                            name="items[{{ $purchaseProduct->product_id }}][product_id]"
                                                                            value="{{ $purchaseProduct->product_id }}">

                                                                        <input type="number"
                                                                            name="items[{{ $purchaseProduct->product_id }}][quantity]"
                                                                            class="form-control return-quantity"
                                                                            value="0" min="0"
                                                                            max="{{ $remainingQty }}"
                                                                            data-max="{{ $remainingQty }}"
                                                                            data-total="{{ $remainingQty * $purchaseProduct->current_net_unit_price }}"
                                                                            required>
                                                                    </td>

                                                                    <td class="unit-price">
                                                                        {{ number_format($purchaseProduct->current_net_unit_price, 2) }}
                                                                        {{ $general->site_currency }}
                                                                    </td>
                                                                    <td class="total-price">
                                                                        {{ number_format($purchaseProduct->current_net_unit_price, 2) }}
                                                                        {{ $general->site_currency }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-4 pt-5">
                                                <label>{{ __('Payment Account') }}</label>
                                                <select class="form-control select2" name="payment_method"
                                                    id="payment_method" required="">
                                                    <option value="" selected disabled>
                                                        {{ __('Select Payment Method') }}
                                                    </option>
                                                    @forelse ($payment_account as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}
                                                            @if ($item->account_number)
                                                                (AC: {{ $item->account_number }})
                                                            @endif
                                                        </option>
                                                    @empty
                                                        <option disabled>{{ __('No record found') }}
                                                        </option>
                                                    @endforelse
                                                </select>
                                                <div class="invalid-feedback">
                                                    {{ __('Please Add a payment account') }}
                                                </div>
                                            </div>
                                            <div class="col-md-2 mt-5">
                                                <label class="form-label">Due</label>
                                                <div class="row gutters-xs">
                                                    <div class="col-auto">
                                                        <label class="colorinput">
                                                            <input name="due" type="checkbox" value="1"
                                                                class="colorinput-input" id="due_button" />
                                                            <span class="colorinput-color bg-primary"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 mt-5">
                                                <label for="note">Return Note:</label>
                                                <textarea name="note" id="note" class="form-control" rows="3" placeholder="Note about the return"
                                                    required=""></textarea>
                                                <div class="invalid-feedback">
                                                    {{ __('Please provide a note.') }}
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Submit button for return -->
                                        <div class="d-flex justify-content-end mt-4">
                                            <button class="btn btn-primary"
                                                type="submit">{{ __('Submit Purchase Return') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        $('.return-quantity').on('input', function() {
            const $this = $(this);
            const maxQuantity = parseInt($this.data('max')) || 0;
            const value = parseInt($this.val()) || 0;

            const total = parseFloat($this.data('total')) || 0;

            const $row = $this.closest('tr');
            const $errorText = $('.error-text');

            if (value > maxQuantity) {
                $errorText.text('Return quantity cannot be greater than available quantity.');
                $this.val(maxQuantity); // Reset to max quantity if exceeded
                return;
            } else {
                $errorText.text('');
            }

            const unitPrice = parseFloat($row.find('.unit-price').text().replace(/[^\d.-]/g, '')) || 0;
            $('#remainQty').text(maxQuantity - value);
            const totalPrice = (unitPrice * value);

            $row.find('.total-price').text(totalPrice.toFixed(2) + ' {{ $general->site_currency }}');
        });

        $(document).ready(function() {
            $('#due_button').change(function() {
                if ($(this).is(':checked')) {
                    $('#payment_method').prop('disabled', true);
                    $('#payment_method').val(null).trigger('change'); // reset selection
                } else {
                    $('#payment_method').prop('disabled', false);
                }
            });
        });
    </script>
@endpush
