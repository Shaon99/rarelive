@extends('backend.layout.master')
@push('style')
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #676767 !important;
            line-height: 40px !important;
            text-transform: capitalize !important;
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
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.purchases.index') }}">{{ __('Purchases') }}</a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.purchases.update', $singlePurchases->id) }}" method="POST"
                                class="needs-validation" novalidate="">
                                @csrf
                                @method('PUT')
                                <div class="row">

                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label class="label" for="code">{{ __('Reference No') }}</label>
                                        <input type="text" name="reference_no" class="form-control" required=""
                                            value="{{ old('reference_no', $singlePurchases->reference_no) }}"
                                            placeholder="{{ __('enter reference no') }}" required="">
                                    </div>
                                    <div class="invalid-feedback">
                                        {{ __('reference no can not be empty') }}
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Invoice No') }}</label>
                                        <input type="text" class="form-control" name="invoice_no"
                                            value="{{ old('invoice_no', $singlePurchases->invoice_no) }}"
                                            placeholder="enter invoice no" required="">
                                        <div class="invalid-feedback">
                                            {{ __('invoice can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Warehouse') }}</label>
                                        <select class="form-control select2" name="warehouse" required="">
                                            <option value="" selected disabled>{{ __('select warehouse') }}</option>
                                            @forelse ($warehouses as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $singlePurchases->warehouse_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}
                                                </option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('warehouse can not be empty') }}
                                        </div>

                                    </div>

                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Supplier') }}</label>
                                        <select class="form-control select2" name="supplier" required="">
                                            <option value="" selected disabled>{{ __('select supplier') }}</option>
                                            @forelse ($suppliers as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $singlePurchases->supplier_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}
                                                </option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('supplier can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Product') }}</label>
                                        <select class="form-control select2" name="product" id="product">
                                            <option value="" selected disabled>{{ __('select product') }}</option>
                                            @forelse ($products as $item)

                                                <option value="{{ $item->id }}" 
                                                    @forelse ($singlePurchases->purchasesProduct as $product)
                                                        {{ $product->product_id==$item->id?'selected disabled':'' }}
                                                    @empty
                                                        
                                                    @endforelse
                                                    
                                                    >
                                                    {{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}
                                                </option>
                                            @endforelse
                                        </select>                                       
                                    </div>
                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Purchase a Date') }}</label>
                                        <input type="text" name="purchase_date"
                                            value="{{ old('purchase_date', $singlePurchases->purchase_date) }}"
                                            class="form-control datepicker">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Order Status') }}</label>
                                        <select class="form-control select2" name="order_status" id="order_status"
                                            required="">
                                            <option value="" selected disabled>{{ __('select Order Status') }}
                                            </option>
                                            <option value="0" {{ $singlePurchases->status == 0 ? 'selected' : '' }}>
                                                {{ __('Recieved') }}</option>
                                            <option value="1" {{ $singlePurchases->status == 1 ? 'selected' : '' }}>
                                                {{ __('Pending') }}</option>
                                            <option value="2" {{ $singlePurchases->status == 2 ? 'selected' : '' }}>
                                                {{ __('Ordered') }}</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('select a order status') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered order-list bg-white" id="invoice">
                                                <thead class="info">
                                                    <tr>
                                                        <th>{{ __('Product Name') }}</th>
                                                        <th>{{ __('Quantity') }}</th>
                                                        <th>{{ __('Unit Price') }}</th>
                                                        <th>{{ __('Sub Total') }}</th>
                                                        <th>{{ __('Delete') }}</th>

                                                    </tr>

                                                </thead>

                                                <tbody>
                                                    <tr class="d-none" id="preloader">
                                                        <th colspan="12">
                                                            <div class="d-flex justify-content-center">
                                                                <div class="spinner-border" role="status">
                                                                    <span class="visually-hidden"></span>
                                                                </div>
                                                            </div>
                                                        </th>
                                                    </tr>

                                                    <tr>
                                                        @forelse ($singlePurchases->purchasesProduct as $item)
                                                        <tr>
                                                            <td class="col-sm-2 product-title">
                                                                <span><strong>{{ $item->product->name }}
                                                                    </strong></span></td>
                                                            <td class="col-sm-3">
                                                                <div class="input-group"><span
                                                                        class="input-group-btn"><button type="button"
                                                                            class="btn btn-default btn-xs minus"><span><i
                                                                                    class="fas fa-minus-circle"></i></span></button></span><input
                                                                        type="text" name="qty[]"
                                                                        class="text-center form-control qty numkey input-number"
                                                                        id="qty-val" value="{{ $item->quantity }}" min="1"
                                                                        step="any" required><span
                                                                        class="input-group-btn"><button type="button"
                                                                            data-id="{{ $item->product_id }}"
                                                                            class="btn btn-default btn-xs plus"><span><i
                                                                                    class="fas fa-plus-circle"></i></span></button></span>
                                                                </div>
                                                            </td>
                                                            <td class="col-sm-2 "><input class="form-control product-price" name="current_net_unit_price[]" value="{{ $item->current_net_unit_price }}"></td>
                                                            <input type="hidden" class="net_unit_price" name="previous_net_unit_price[]" value="{{ $item->previous_net_unit_price }}" />
                                                            <td class="col-sm-2 sub-total">{{ $item->total }}</td>
                                                            <td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-xs"><span><i class="fas fa-times"></i></span></button></td>
                                                            <input type="hidden" class="product-id" name="product_id[]" value="{{ $item->product_id }}"/>
                                                        </tr>
                                                            @empty
                                                        @endforelse
                                                    </tr>

                                                </tbody>

                                                <tfoot>
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th>{{ __('Items') }} <span class="text-center"
                                                                id="total_item">{{ $singlePurchases->total_qty }}
                                                            </span></th>

                                                        <input type="text" hidden id="total_qty" value="{{ $singlePurchases->total_qty }}" name="total_qty">

                                                        <th>{{ __('Subtotal') }} <span class="text-center"
                                                                id="total_sub">{{ $singlePurchases->sub_total }}
                                                                {{ @$general->site_currency }}</span></th>
                                                        <input type="text" hidden id="sub_totals" value="{{ $singlePurchases->sub_total }}" name="subtotals">
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">

                                        <label for="tax" class="control-label">{{ __('Discount') }}</label>
                                        <input type="number" class="form-control" min="0" step="0.05"
                                            value="{{ $singlePurchases->discount }}" name="discount" id="discount" placeholder="Enter Discount">
                                    </div>


                                    <div class="col-md-4">

                                        <div class="form-group text-bold">
                                            <label for="shipping"
                                                class="control-label">{{ __('Shipping Charge') }}</label>

                                            <div class="input-group">
                                                <input type="number" class="form-control" min="0" step="0.50"
                                                    value="{{ $singlePurchases->other_cost }}" name="shipping" id="shipping"
                                                    placeholder="Enter Shipping">
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group text-bold">
                                            <label for="tax" class="control-label">{{ __('Payment By') }}</label>
                                            <select class="form-control select2" name="payment_by" id="payment_by"
                                                required=''>
                                                <option value="" selected disabled>{{ __('Select Payment Method') }}
                                                </option>
                                                <option value="Cash" {{ $singlePurchases->payment_method == 'Cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                                <option value="Check" {{ $singlePurchases->payment_method == 'Check' ? 'selected' : '' }}>{{ __('Check') }}</option>
                                                <option value="Card" {{ $singlePurchases->payment_method == 'Card' ? 'selected' : '' }}>{{ __('Card') }}</option>
                                                <option value="Not Paid" {{ $singlePurchases->payment_method == 'Not Paid' ? 'selected' : '' }}>{{ __('Not Paid') }}</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ __('please select a payment method') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group  text-bold">
                                            <label for="payment" class="control-label">{{ __('Paying Amount') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" min="0" step="any"
                                                    value="{{ $singlePurchases->paid_amount }}" name="payment" id="payment"
                                                    placeholder="Enter payment">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group text-bold">
                                            <label for="payment" class="control-label">{{ __('Due Amount') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" value="{{ $singlePurchases->due_amount }}"
                                                    name="duepayment" id="duepayment" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 py-4">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-responsivebg-light">
                                                <tfoot>
                                                    <tr>
                                                        <th> {{ __('Items') }} <span class="pull-right"
                                                                id="total_items">{{ $singlePurchases->total_qty }}</span></th>
                                                        <th> {{ __('Total') }} <span class="pull-right"
                                                                id="total_value">{{ $singlePurchases->sub_total }}
                                                            </span> {{ @$general->site_currency }}</th>
                                                        <th> {{ __('Discount') }} <span class="pull-right"
                                                                id="total_discount">{{ $singlePurchases->discount }}
                                                            </span> {{ @$general->site_currency }}</th>

                                                        <th> {{ __('Shipping') }} <span class="pull-right"
                                                                id="total_shipping">{{ $singlePurchases->other_cost }}
                                                            </span> {{ @$general->site_currency }}</th>
                                                        <th> {{ __('Grand Total') }} <span class="pull-right"
                                                                id="grand_total">{{ $singlePurchases->grand_total }}
                                                            </span> {{ @$general->site_currency }}</th>
                                                        <th> {{ __('Due Amount') }} <span class="pull-right"
                                                                id="dueamount">{{ $singlePurchases->due_amount }} </span>
                                                            {{ @$general->site_currency }}
                                                        </th>

                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <input type="hidden" class="sub_total" name="sub_total" value="{{ $singlePurchases->sub_total }}" />
                                <input type="hidden" class="dis_total" name="dis_total" value="{{ $singlePurchases->discount }}" />
                                <input type="hidden" class="grand_total" name="grand_total" value="{{ $singlePurchases->grand_total }}" />
                                <div>
                                    <button class="btn btn-primary" type="submit">{{ __('Update Purchases') }}</button>
                                </div>
                        </div>
                        </form>
                    </div>

                </div>

            </div>


        </section>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $(document).on('change', '#product', function(e) {
                var id = $('#product').val();
                var url = "{{ route('admin.product.edit', ':id') }}";
                url = url.replace(':id', id);

                $.ajax({
                    type: 'GET',
                    url: url,
                    beforeSend: function() {
                        $("#preloader").removeClass('d-none');
                    },
                    complete: function() {
                        $("#preloader").addClass('d-none');
                    },
                    success: (data) => {

                        $("#product option:selected").attr('disabled', 'disabled');

                        newDataAppend(data);
                        calculateTotal();
                    }

                })

            });

        });
    </script>

    <script src="{{ asset('asset/admin/js/purchase.js') }}"></script>
@endpush
