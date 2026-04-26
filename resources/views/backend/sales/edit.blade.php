@extends('backend.layout.master')
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/sales.css') }}">
@endpush
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="loading-overlay loading-show d-none">
            </div>
            <form id="salesForm" action="{{ route('admin.sales.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row" style="margin-right: -9px; margin-left: -9px;">
                    <div class="col-md-6 col-12 border-r card p-2 mb-2 rounded">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row align-items-center">
                                    <div class="col-md-6 col-12 col-sm-12 mb-1">
                                        <input type="text" class="form-control searchInputBox" name="customer_phone"
                                            id="phoneInput" placeholder="Enter Customer Phone Number"
                                            value="{{ $order->customer->phone ?? '' }}">
                                        <small class="error-phone text-danger"></small>
                                    </div>
                                    <div class="col-md-3 col-6 col-sm-6 mb-2 text-md-right">
                                        @if ($general->fraud_check_on_off == 1)
                                            <button type="button"
                                                class="fraudCheck btn btn-primary btn-icon btn-sm w-100 w-md-auto">
                                                <i class="fas fa-triangle-exclamation"></i> Fraud Check
                                            </button>
                                        @endif
                                    </div>
                                    <div class="col-md-3 col-6 col-sm-6 mb-2 text-md-right">
                                        <button type="button" id="checkCustomer"
                                            class="btn btn-primary btn-icon btn-sm w-100 w-md-auto">
                                            <i class="fas fa-user"></i> Customer
                                        </button>
                                    </div>
                                </div>

                                <input type="text" hidden id="address_input" name="address"
                                    value="{{ $order->delivery_address ?? '' }}" class="form-control">
                                <small class="error-address text-danger"></small>

                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <small
                                        class="metric-label text-dark name-show me-2">{{ $order->customer->name ?? '' }}</small>
                                    <small
                                        class="metric-label text-dark phone-show me-2">{{ $order->customer->phone ?? '' }}</small>
                                    <small
                                        class="metric-label text-dark address-show">{{ $order->delivery_address ?? '' }}</small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <small class="error-table text-danger"></small>
                                <div class="table-responsive product-scroll-table">
                                    <table id="sale" class="table order-list table-fixed">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Price') }}</th>
                                                <th>{{ __('Discount') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Total') }}</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->salesProduct as $index => $item)
                                                <tr>
                                                    <td class="product-title">
                                                        <span class="product-name text-capitalize">{{ $loop->iteration }}.
                                                            {{ $item->product->name ?? '' }}</span>
                                                    </td>

                                                    <td>
                                                        <input class="product-price" name="sale_price[]"
                                                            value="{{ number_format($item->unit_price, 2) }}" />
                                                    </td>

                                                    <td>
                                                        <input class="discount-price" name="discount_price[]"
                                                            value="{{ number_format($item->discount ?? 0, 2) }}" />
                                                        <span class="discount-type-indicator">
                                                            {{ $item->discount_type == 'percentage' ? '%' : 'Fixed' }}
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <span>
                                                            <button type="button" class="minus">
                                                                <i class="fas fa-minus-circle text-primary"></i>
                                                            </button>
                                                        </span>
                                                        <input type="text" name="qty[]" class="qty numkey input-number"
                                                            value="{{ $item->quantity ?? 1 }}" min="1"
                                                            step="any" required>
                                                        <span>
                                                            <button type="button" data-id="{{ $item->product_id }}"
                                                                class="plus">
                                                                <i class="fas fa-plus-circle text-primary"></i>
                                                            </button>
                                                        </span>
                                                    </td>

                                                    <td class="sub-total">
                                                        {{ number_format($item->total, 2) }}
                                                    </td>

                                                    <td class="text-center">
                                                        <a type="button" class="ibtnDel text-center">
                                                            <i class="fas fa-xmark-circle text-danger text-end"></i>
                                                        </a>
                                                    </td>

                                                    {{-- Hidden fields --}}
                                                    <input type="hidden" class="product-id" name="product_id[]"
                                                        value="{{ $item->product_id }}" />
                                                    <input type="hidden" class="net_unit_price" name="net_unit_price[]"
                                                        value="{{ $item->unit_price }}" />
                                                    <input type="hidden" class="combo" name="type[]"
                                                        value="{{ $item->type ?? 'single' }}" />
                                                    <input type="hidden" class="productqty"
                                                        value="{{ $item->branch_quantity ?? 99999 }}" />
                                                    <input type="hidden" class="discount_type" name="discount_type[]"
                                                        value="{{ $item->discount_type }}" />
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" name="item" value="{{ $order->total_qty }}" />
                                <input type="hidden" name="subtotal" value="{{ $order->sub_total }}" />
                                <input type="hidden" name="grand_total" value="{{ $order->grand_total }}" />
                            </div>

                            <div class="col-md-12 d-block d-md-none mt-3">
                                <div class="d-block d-md-none mb-2" id="openModal">
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-toggle="modal"
                                        data-target="#addProductModal">
                                        <i class="fas fa-plus-circle"></i> Add Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 d-none d-md-block">
                        <div class="row">
                            <livewire:search-pos-product />
                        </div>
                    </div>
                    <div class="col-md-12 col-12">
                        <div class="row sticky-footer">
                            <div class="col-md-4 col-12">
                                <div class="row">
                                    <div class="col-md-6 col-6 mt-1">
                                        <div class="d-flex justify-content-between px-2">
                                            <h6 class="subtotal-text">Items</h6>
                                            <h6 class="subtotal-text"><span id="item">{{ $order->total_qty }}</span>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-6 mt-1">
                                        <div class="d-flex justify-content-between px-2">
                                            <h6 class="subtotal-text">SubTotal</h6>
                                            <h6 class="subtotal-text">
                                                <span id="subtotal">{{ number_format($order->sub_total, 2) }}</span>
                                                <span class="ml-1">{{ @$general->site_currency }}</span>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 d-none">
                                        <span class="totals-title text-center">Discount</span><span
                                            id="total_discount">0.00</span>
                                    </div>
                                    <div class="col-sm-3 d-none">
                                        <span class="totals-title">Shipping Cost</span><span id="shipping">0.00</span>
                                    </div>
                                    <div class="col-sm-6 col-6 mt-2">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="totals-title input-group-text searchInputBox">Discount</span>
                                            </div>
                                            <input type="number" value="{{ $order->discount }}" name="discount"
                                                class="form-control searchInputBox" id="discount"
                                                placeholder="Flat discount">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-6 mt-2">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="totals-title input-group-text searchInputBox">Shipping</span>
                                            </div>
                                            <input type="number" value="{{ $order->system_delivery_charge ?? 0 }}"
                                                name="shipping_cost" id="shipping_cost"
                                                class="form-control searchInputBox" placeholder="Shipping amount">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-6 mt-2">
                                        <textarea name="note" rows="1" class="form-control searchInputBox" placeholder="Special note here..."
                                            id="note">{{ $order->note ?? '' }}</textarea>
                                    </div>
                                    <div class="col-sm-6 col-6 mt-2">
                                        <h6
                                            class="grand-total text-dark p-2 searchInputBox d-flex justify-content-between">
                                            {{ __('Total') }}
                                            <span>
                                                <span id="grand_totals">{{ number_format($order->grand_total, 2) }}</span>
                                                <span>{{ $general->site_currency }}</span>
                                            </span>
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side -->
                            <div class="col-md-8 col-12">
                                <div class="row">
                                    @if ($general->pos_platform_on_off == 1)
                                        <div class="col-md-2 col-6">
                                            <label for="tax" class="control-label">{{ __('Platform') }}</label>
                                            <select class="form-control select2"
                                                data-minimum-results-for-search="Infinity" name="platform" id="platform"
                                                required>
                                                <option value="" selected disabled>{{ __('Platform') }}</option>
                                                <option value="facebook"
                                                    {{ $order->platform == 'facebook' ? 'selected' : '' }}>
                                                    {{ __('Facebook') }}</option>
                                                <option value="whatsapp"
                                                    {{ $order->platform == 'whatsapp' ? 'selected' : '' }}>
                                                    {{ __('Whatsapp') }}</option>
                                                <option value="others"
                                                    {{ $order->platform == 'others' ? 'selected' : '' }}>
                                                    {{ __('Others') }}</option>
                                            </select>
                                            <small class="error-platform text-danger"></small>
                                        </div>
                                    @endif
                                    @if ($general->pos_lead_on_off == 1)
                                        <div class="col-md-2 col-6">
                                            <label for="tax" class="control-label">{{ __('Order Lead') }}</label>
                                            <select class="form-control select2" name="lead" id="lead"
                                                required>
                                                <option value="" selected disabled>{{ __('Lead') }}</option>
                                                @foreach ($employee as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ $item->id == $order->lead_id ? 'selected' : '' }}>
                                                        {{ $item->employee_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="error-lead text-danger"></small>
                                        </div>
                                    @endif

                                    <div class="col-md-2 col-6">
                                        <label for="payment" class="control-label">{{ __('Paying') }}</label>
                                        <input type="number" class="form-control" step="any"
                                            value="{{ $order->paid_amount ?? 0 }}" name="payment" id="payment">
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <label for="payment" class="control-label">{{ __('Due') }}</label>
                                        <input type="number" class="form-control" value="{{ $order->due_amount ?? 0 }}"
                                            name="duepayment" id="duepayment" readonly>
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <label>{{ __('Payment') }}</label>
                                        <select class="form-control" name="payment_by" id="payment_by" required>
                                            <option value="" selected disabled>{{ __('Select Payment Method') }}
                                            </option>
                                            @foreach ($payment_account as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $item->id == $order->payment_method ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                    @if ($item->account_number)
                                                        (AC: {{ $item->account_number }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="error-payment-by text-danger"></small>
                                    </div>

                                    <div class="col-md-2 col-6 mt-2 d-flex">
                                        <div class="mr-3">
                                            <label class="control-label">{{ __('Due') }}</label>
                                            <label class="colorinput mb-0">
                                                <input name="due" type="checkbox" value="1"
                                                    class="colorinput-input" id="due_button" />
                                                <span class="colorinput-color bg-danger"></span>
                                            </label>
                                        </div>

                                        @if ($general->enable_online_deliver == 1)
                                            <div>
                                                <label class="control-label">{{ __('STEADFAST') }}</label>
                                                <label class="colorinput mb-0">
                                                    <input name="steadFast" type="checkbox" value="1"
                                                        class="colorinput-input" id="courier" />
                                                    <span class="colorinput-color bg-primary"></span>
                                                </label>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-12 mb-2 mt-3 text-right">
                                        <div class="row">
                                            <div class="col-6 col-sm-6 col-md-6 mb-2 mb-md-0">
                                                <div
                                                    class="d-flex align-items-center justify-content-start justify-content-md-end">
                                                    <label class="control-label mr-2">{{ __('Save') }}</label>
                                                    <label class="colorinput mb-0">
                                                        <input name="draft" type="checkbox" value="1"
                                                            class="colorinput-input" id="draft" />
                                                        <span class="colorinput-color bg-primary"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6">
                                                <button type="submit" id="submitBtn" class="btn btn-primary w-100">
                                                    <i class="fas fa-save mr-2"></i> {{ __('Save Changes') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <!-- existing customer Modal-->
    <div id="customerModal" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1"
        role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Customer Information') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-1">
                    <div class="loading-overlay loading-show d-none">
                        please wait...
                    </div>
                    <p class="mb-1"><strong>Name:</strong> <span id="customerName"></span></p>
                    <p class="mb-1"><strong>Email:</strong> <span id="customerEmail"></span></p>
                    <p class="mb-1"><strong>Phone:</strong> <span id="customerPhone"></span></p>
                    <p class="mb-1"><strong>Phone:</strong> <span id="customerPhone"></span></p>
                    <p class="mb-1"><strong>Addresses:</strong></p>
                    <form id="addressForm">
                        <div id="addressOptions"></div>
                    </form>
                    <div class="form-group py-2">
                        <p for="newAddress" class="mb-2">Add New Address:</p>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>{{ __('City') }}</label>
                                <select class="form-control select2" name="city" id="citySelect">
                                    <option value="" selected disabled>{{ __('Select City') }}</option>
                                    @forelse ($districts as $item)
                                        <option value="{{ $item['name'] }}">
                                            {{ $item['name'] }}
                                            {{ isset($item['bn_name']) && $item['bn_name'] ? '(' . $item['bn_name'] . ')' : '' }}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>{{ __('Thana') }}</label>
                                <select class="form-control select2" name="thana" id="thanaSelect" disabled>
                                    <option value="" selected disabled>{{ __('Select Thana') }}</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="">{{ __('Full Address') }}</label>
                                <textarea name="address" id="newAddress" rows="5" value="{{ old('address') }}" class="form-control"
                                    placeholder="Enter Full Address City, Thana, Address" required=""></textarea>
                                <small class="error-add-new text-danger"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer pt-2">
                    <button type="button" id="saveAddress" class="btn btn-primary">Save Address</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Create Modal-->
    <div class="modal fade" tabindex="-1" id="customer-create" data-backdrop="static" data-keyboard="false"
        role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form action="" id="customer-create-form" method="POST" class="needs-validation" novalidate="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add New Customer') }} <span class="name"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($general->fraud_check_on_off == 1)
                            <button type="button" class="btn btn-primary fraudCheck btn-sm btn-icon mb-2">
                                <i class="fas fa-light fa-triangle-exclamation"></i> Fraud Check
                            </button>
                        @endif
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="">{{ __('Name') }}</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    placeholder="Enter name" required="">
                                <small class="text-danger errorname"></small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="">{{ __('Phone') }}</label>
                                <input type="text" class="form-control" name="phone" id="phone"
                                    placeholder="Enter phone" required="">
                                <small class="text-danger errorphone"></small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="">{{ __('Email') }}</label>
                                <input type="text" class="form-control" id="Email" name="Email"
                                    placeholder="Enter email">
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label>Social Platform / Order From</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="social_type"
                                            id="facebookRadio" value="facebook">
                                        <label class="form-check-label" for="facebookRadio">Facebook</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="social_type"
                                            id="whatsappRadio" value="whatsapp">
                                        <label class="form-check-label" for="whatsappRadio">WhatsApp</label>
                                    </div>
                                </div>

                                {{-- Social ID Input (hidden by default) --}}
                                <div id="socialIdInput" class="mt-2 d-none">
                                    <label id="socialIdLabel">Facebook ID</label>
                                    <input type="text" name="social_id" id="social_id" class="form-control"
                                        placeholder="Enter Facebook ID">
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label>{{ __('City') }}</label>
                                <select class="form-control select2" name="city" id="citySelect2Create">
                                    <option value="" selected disabled>{{ __('Select City') }}</option>
                                    @forelse ($districts as $item)
                                        <option value="{{ $item['name'] }}">
                                            {{ $item['name'] }}
                                            {{ isset($item['bn_name']) && $item['bn_name'] ? '(' . $item['bn_name'] . ')' : '' }}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group col-md-6 mb-2">
                                <label>{{ __('Thana') }}</label>
                                <select class="form-control select2" name="thana" id="thanaSelect2Create" disabled>
                                    <option value="" selected disabled>{{ __('Select Thana') }}</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="">{{ __('Full Address') }}</label>
                                <textarea name="address" id="address" rows="5" value="{{ old('address') }}" class="form-control"
                                    placeholder="Enter Full Address City, Thana, Address" required=""></textarea>
                                <small class="text-danger errorAddress"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer pt-0">
                        <button type="button" class="btn btn-secondary closeBtn"
                            data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary submitBtn">{{ __('Create') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div id="fraudModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fraudModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fraudModalLabel">Courier History Check</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="fraudModalBody">
                    <!-- Result will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile device product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addProductModalLabel">Add Product</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @livewire('search-pos-product')
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="{{ asset('assets/admin/js/sales.js') }}"></script>
    <script src="{{ asset('assets/admin/js/JsBarcode.all.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/qrcode.min.js') }}"></script>
    <script>
        window.POS_INVOICE_ENABLED = {{ $general->pos_invoice_on_off ? 'true' : 'false' }};
    </script>
    <script>
        'use strict';
        $(document).ready(function() {
            $('#draft').change(function() {
                if ($(this).is(':checked')) {
                    $('#courier').prop('disabled', true).prop('checked', false).closest('div').hide();
                    $('#due_button').prop('disabled', true).prop('checked', false).closest('div').hide();
                } else {
                    $('#courier').prop('disabled', false).closest('div').show();
                    $('#due_button').prop('disabled', false).closest('div').show();
                }
            });
            $('#draft').trigger('change');
        });

        $(document).ready(function() {
            var isChecked = $('#courier').prop('checked');
            var due_button = $('#due_button').prop('checked');

            //product click
            $(document).on("click", "#product-img", function() {
                var product = $(this).data('product');
                var branchQuantity = $(this).data('quantity');
                if ($('#phoneInput').val() == '') {
                    showToast('Please add a customer first', 'error')
                    return false;
                }
                if (product) {
                    var type = 'product';
                    var flag = 1;
                    $(".product-id").each(function(i) {
                        if ($(this).val() == product.id) {
                            showToast("Product already has been added! increase quantity",
                                'error');
                            flag = 0;
                        }
                    });
                    if (flag) {
                        newDataAppend(product, type, branchQuantity);
                    }
                    calculateTotal();
                } else {
                    showToast("Product not found", 'error');
                }
            });
            //combo product click
            $(document).on("click", "#combo-product-img", function() {
                var combo = $(this).data('combo-product');
                var comboQuantity = $(this).data('combo-quantity');
                if ($('#phoneInput').val() == '') {
                    showToast('Please add a customer first', 'error')
                    return false;
                }
                if (combo) {
                    var type = 'combo';
                    var flag = 1;
                    $(".c-product-id").each(function(i) {
                        if ($(this).val() == combo.id) {
                            showToast("Product already has been added! increase quantity",
                                'error');
                            flag = 0;
                        }
                    });
                    if (flag) {
                        newDataAppend(combo, type, comboQuantity);
                    }
                    calculateTotal();
                } else {
                    showToast("Product not found", 'error');
                }
            });

            $('input[name="social_type"]').on('change', function() {
                const selected = $(this).val();

                // Clear the previous value in the input field
                $('#socialIdInput input').val('');

                // Show the input field
                $('#socialIdInput').removeClass('d-none');

                // Change label and placeholder based on the selected social type
                if (selected === 'whatsapp') {
                    $('#socialIdLabel').text('WhatsApp Number');
                    $('#socialIdInput input').attr('placeholder', 'Enter WhatsApp Number');
                    $('#socialIdInput input').attr('type', 'number'); // Change to number type for WhatsApp
                } else if (selected === 'facebook') {
                    $('#socialIdLabel').text('Facebook ID');
                    $('#socialIdInput input').attr('placeholder', 'Enter Facebook Short Url');
                    $('#socialIdInput input').attr('type', 'text'); // Change to text type for Facebook
                }
            });
            //customer create
            $(document).on('submit', '#customer-create-form', function(e) {
                e.preventDefault();
                let form = $(this);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.customer.store') }}",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        $('.name-show').text($('#name').val());
                        $('.phone-show').text($('#phone').val());
                        $('.address-show').text(data.customer.address + ', ' + data.customer
                            .city + ', ' + data.customer.thana);

                        showToast("Customer added successfully", 'success');

                        // Copy address value elsewhere if needed
                        $('#address_input').val($('#address').val());

                        // Reset form inputs
                        form.trigger("reset");

                        // Clear validation messages
                        $('.invalid-feedback-e').text('');
                        $('.errorname').text('');
                        $('.errorphone').text('');
                        $('.errorAddress').text('');

                        // Reset select2 and disable thana
                        $('#citySelect2Create').val('').trigger('change');
                        $('#thanaSelect2Create').empty().append(
                                '<option value="" selected disabled>Select Thana</option>')
                            .prop('disabled', true);

                        // Remove was-validated class if added
                        form.removeClass('was-validated');

                        // Close modal
                        $('.closeBtn').click();
                    },
                    error: function(error) {
                        if (error.responseJSON?.errors?.phone) {
                            $('.errorphone').text(error.responseJSON.errors.phone);
                        } else {
                            $('.errorphone').text('');
                        }
                        if (error.responseJSON?.errors?.name) {
                            $('.errorname').text(error.responseJSON.errors.name);
                        } else {
                            $('.errorname').text('');
                        }
                        if (error.responseJSON?.errors?.address) {
                            $('.errorAddress').text(error.responseJSON.errors.address);
                        } else {
                            $('.errorAddress').text('');
                        }

                        // Optionally, show validation styling
                        form.addClass('was-validated');
                        submitBtn.prop('disabled', false).removeClass('btn-progress');
                    }
                });
            });

            // Reset modal state when closed
            $('#customer-create').on('hidden.bs.modal', function() {
                const form = $('#customer-create-form');

                form.trigger('reset');
                form.removeClass('was-validated');

                $('.errorname').text('');
                $('.errorphone').text('');
                $('.errorAddress').text('');

                $('#citySelect2Create').val('').trigger('change');
                $('#thanaSelect2Create').empty().append(
                    '<option value="" selected disabled>Select Thana</option>').prop('disabled', true);
                form.find('.submitBtn').prop('disabled', false).removeClass('btn-progress');

            });

            let lastPhoneNumber = '';
            let lastCustomerFound = false;

            function fetchCustomer(phone) {
                if (phone === '') return;

                const bangladeshiPhoneRegex = /^(?:\+88|88)?01[3-9]\d{8}$/;
                if (!bangladeshiPhoneRegex.test(phone)) {
                    $('.error-phone').text('Phone number is invalid!');
                    return;
                }

                $('.error-phone').text('');

                $.ajax({
                    url: '{{ route('admin.search.customer') }}',
                    beforeSend: function() {
                        $(".loading-show").removeClass('d-none');
                    },
                    complete: function() {
                        $(".loading-show").addClass('d-none');
                    },
                    method: 'POST',
                    data: {
                        phone: phone,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if (response.status === 'found') {
                            lastCustomerFound = true; // Customer found

                            const customer = response.customer;
                            // Populate modal with customer details
                            $('#customerName').text(customer.name);
                            $('#customerEmail').text(customer.email ?? 'N/A');
                            $('#customerPhone').text(customer.phone);

                            // Reset error messages
                            $('.error-payment-by, .error-phone, .error-address, .error-table')
                                .text('');

                            // Populate addresses as radio buttons
                            const addressOptions = $('#addressOptions');
                            addressOptions.empty(); // Clear previous options
                            customer.address_books.forEach((address, index) => {
                                addressOptions.append(`
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="address" id="address${index}" value="${address.address}">
                                        <p class="form-check-label" for="address${index}">
                                            ${address.address} 
                                        </p>
                                    </div>
                                `);
                            });

                            // Handle address selection
                            addressOptions.off('change').on('change', 'input[name="address"]',
                                function() {
                                    const selectedValue = $('input[name="address"]:checked')
                                        .val();
                                    $('#address_input').val(selectedValue);
                                    $('.invalid-feedback-e').text('');
                                    $('.name-show').text(customer.name);
                                    $('.phone-show').text(customer.phone);
                                    $('.address-show').text(selectedValue);
                                    $('#customerModal').modal('hide');
                                });

                            $('#customerModal').modal('show');
                        } else {
                            lastCustomerFound = false; // No customer found
                            $('#phone').val(phone);
                            $('#address_input').val('');
                            $('#customer-create').modal('show');
                        }
                    },
                    error: function() {
                        console.log('An error occurred while searching for the customer.');
                    },
                });
            }
            // Trigger modal when phone input changes or loses focus
            $('#phoneInput').on('change', function() {
                const phone = $(this).val().trim();

                if (phone === lastPhoneNumber && lastCustomerFound) {
                    // If the number is the same and a customer was found, show the modal
                    $('#customerModal').modal('show');
                    return;
                }

                $('.name-show').text('');
                $('.phone-show').text('');
                $('.address-show').text('');
                $('#name').val('');
                $('#phone').val('');
                $('#email').val('');
                $('#address_input').val('');
                $('.error-add-new').text('');
                lastPhoneNumber = phone;
                fetchCustomer(phone);
            });

            $('#citySelect').select2({
                dropdownParent: $('#customerModal')
            });
            $('#thanaSelect').select2({
                dropdownParent: $('#customerModal')
            });

            $('#citySelect2Create').select2({
                dropdownParent: $('#customer-create')
            });
            $('#thanaSelect2Create').select2({
                dropdownParent: $('#customer-create')
            });

            $('#checkCustomer').on('click', function() {
                const phone = $('#phoneInput').val().trim();

                if (phone === '') {
                    $('.error-phone').text('Please provide a valid phone number.');
                }

                if (phone === lastPhoneNumber && lastCustomerFound) {
                    // If the number is the same and a customer was found, show the modal
                    $('#customerModal').modal('show');
                    return;
                }

                $('.error-add-new').text('');
                lastPhoneNumber = phone;
                fetchCustomer(phone);
            });
            // Reset last phone number when modal is closed (to allow re-opening)
            $('#customerModal').on('hidden.bs.modal', function() {
                lastPhoneNumber = '';
                lastCustomerFound = false;
            });

            // Save new address for the customer
            $('#saveAddress').on('click', function() {
                const newAddress = $('#newAddress').val();
                const phone = $('#customerPhone').text();
                let city = $('#citySelect').val();
                let thana = $('#thanaSelect').val();

                if (!newAddress) {
                    $('.error-add-new').text('New address cannot be empty.');
                    return false;
                } else {
                    $('.error-add-new').text(''); // Clear previous error
                }

                $.ajax({
                    url: '{{ route('admin.add.address') }}',
                    beforeSend: function() {
                        $(".loading-show").removeClass('d-none');
                    },
                    complete: function() {
                        $(".loading-show").addClass('d-none');
                    },
                    method: 'POST',
                    data: {
                        phone: phone,
                        address: newAddress,
                        city: city,
                        thana: thana,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            const newAddressIndex = response
                                .newAddressIndex;

                            const newAddress = $('#newAddress').val(response.latestAddress);

                            // Append the new address as a radio button
                            $('#addressOptions').append(`
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="address" id="address${newAddressIndex}" value="${response.latestAddress}">
                                        <p class="form-check-label" for="address${newAddressIndex}">
                                            ${response.latestAddress}
                                        </p>
                                    </div>
                                `);

                            // Clear the input field and show a success message
                            $('#newAddress').val('');

                            $('#citySelect').val('').trigger('change'); // Clear city
                            $('#thanaSelect').val('').trigger('change'); // Clear thana

                            // Optionally 

                            showToast("Address added successfully", 'success');

                        } else {
                            console.log('Failed to add address.');
                        }
                    },
                    error: function() {
                        console.log('An error occurred while adding the address.');
                    },
                });

            });

            //sales submit
            $('#salesForm').submit(function(event) {
                event.preventDefault();
                let phone = $('#phoneInput').val().trim();
                let address = $('#address_input').val().trim();
                let paymentMethod = $('#payment_by').val();
                let submitBtn = $('#submitBtn');

                let platform = $('#platform');
                let lead = $('#lead');

                let hasError = false;

                if (platform.length && platform.val() === '') {
                    $('.error-platform').text('Platform is required');
                    hasError = true;
                } else {
                    $('.error-platform').text('');
                }

                if (lead.length && lead.val() === '') {
                    $('.error-lead').text('Lead is required');
                    hasError = true;
                } else {
                    $('.error-lead').text('');
                }

                if (phone === '') {
                    $('.error-phone').text('Phone number is invalid');
                    hasError = true;
                } else {
                    $('.error-phone').text('');
                }

                if (address === '' || address.length < 5) {
                    $('.error-address').text(
                        'Customer address required. Please enter phone number or add customer from modal'
                    );
                    hasError = true;
                } else {
                    $('.error-address').text('');
                }

                if ($('#sale tbody tr').length === 0) {
                    $('.error-table').text('Please add some products to the order cart');
                    hasError = true;
                } else {
                    $('.error-table').text('');
                }

                if (hasError) return false;

                submitBtn.prop('disabled', true).addClass('btn-progress');

                let form = $(this);
                let formData = form.serialize();

                // Optionally, manually handle the checkbox if needed
                if ($('#courier').prop('checked')) {
                    formData += '&steadFast=1';
                } else {
                    formData += '&steadFast=0';
                }

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        showToast(response.message, 'success');
                        //after redirect
                        setTimeout(function() {
                            window.location.href = "{{ route('admin.sales.index') }}";
                        }, 1000);
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showToast(errorMessage, 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).removeClass('btn-progress');
                    }
                });
            });
        });

        $(document).ready(function() {
            $('#citySelect').on('change', function() {
                var cityName = $(this).val();

                if (cityName) {
                    $.ajax({
                        url: "/get-thana",
                        type: "GET",
                        data: {
                            cityName: cityName
                        },
                        success: function(response) {
                            $('#thanaSelect')
                                .prop('disabled', false)
                                .html('<option value="">Select Thana</option>');

                            if (response && response.length > 0) {
                                const optionsHtml = response.map(item =>
                                    `<option value="${item.name}">${item.name} ${item.bn_name?'( '+ item.bn_name + ' )':''}</option>`
                                ).join('');
                                $('#thanaSelect').append(optionsHtml);
                            } else {
                                $('#thanaSelect')
                                    .html('<option value="">No Thana Found</option>')
                                    .prop('disabled', true);
                            }
                        },
                        beforeSend: function() {
                            $('#thanaSelect').prop('disabled', true).html(
                                '<option value="">Loading...</option>');
                        },
                        error: function() {
                            $('#thanaSelect').html(
                                '<option value="">Error loading data</option>').prop(
                                'disabled', true);
                        }

                    });
                } else {
                    $('#thanaSelect').html('<option value="">Select Thana</option>');
                }
            });

            $('#citySelect2Create').on('change', function() {
                var cityName = $(this).val();

                if (cityName) {
                    $.ajax({
                        url: "/get-thana",
                        type: "GET",
                        data: {
                            cityName: cityName
                        },
                        success: function(response) {
                            $('#thanaSelect2Create')
                                .prop('disabled', false)
                                .html('<option value="">Select Thana</option>');

                            if (response && response.length > 0) {
                                const optionsHtml = response.map(item =>
                                    `<option value="${item.name}">${item.name} ${item.bn_name?'( '+ item.bn_name + ' )':''}</option>`
                                ).join('');
                                $('#thanaSelect2Create').append(optionsHtml);
                            } else {
                                $('#thanaSelect2Create')
                                    .html('<option value="">No Thana Found</option>')
                                    .prop('disabled', true);
                            }
                        },
                        beforeSend: function() {
                            $('#thanaSelect2Create').prop('disabled', true).html(
                                '<option value="">Loading...</option>');
                        },
                        error: function() {
                            $('#thanaSelect2Create').html(
                                '<option value="">Error loading data</option>').prop(
                                'disabled', true);
                        }

                    });
                } else {
                    $('#thanaSelect2Create').html('<option value="">Select Thana</option>');
                }
            });

            function getCourierColor(courier) {
                switch (courier.toLowerCase()) {
                    case 'pathao':
                        return '#e83434';
                    case 'steadfast':
                        return '#00b795';
                    case 'redx':
                        return '#000000';
                    case 'paperfly':
                        return '#00adee';
                    default:
                        return '#32cd32';
                }
            }

            $('.fraudCheck').on('click', function() {
                let phoneNumber = $('#phoneInput').val().trim();
                if (!phoneNumber) {
                    $('.error-phone').text('Please provide a valid phone number.');
                    return false;
                }

                const bangladeshiPhoneRegex = /^(?:\+88|88)?01[3-9]\d{8}$/;
                if (!bangladeshiPhoneRegex.test(phoneNumber)) {
                    $('.error-phone').text('Phone number is invalid!');
                    return false;
                }

                $('.error-phone').text('');


                // Show the modal
                $('#fraudModal').modal('show');
                $('#fraudModalBody').html(`
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3">Loading...</p>
                    </div>
                `);

                $.ajax({
                    url: '{{ route('admin.fraudCheck') }}',
                    method: 'GET',
                    data: {
                        phoneNumber: phoneNumber
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            let resultHtml = '';
                            resultHtml += `
                                <table class="courier-table">
                                    <thead>
                                        <tr>
                                            <th>Courier Name</th>
                                            <th>Total Parcels</th>
                                            <th>Success Parcels</th>
                                            <th>Cancelled Parcels</th>
                                            <th>Success Ratio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                            for (let courier in response.courierData) {
                                let data = response.courierData[courier];

                                resultHtml += `
                                    <tr>
                                        <td class="text-uppercase" style="color: ${getCourierColor(courier)};">${courier}</td>
                                        <td>${data.total_parcel}</td>
                                        <td>${data.success_parcel}</td>
                                        <td>${data.cancelled_parcel}</td>
                                        <td>${data.success_ratio}%</td>
                                    </tr>
                                `;
                            }

                            resultHtml += `
                                    </tbody>
                                </table>
                            `;

                            // Add reports if available
                            if (response.reports.length > 0) {
                                resultHtml += `
                                    <div class="reports-section">
                                        <h5>Reports:</h5>
                                        <ul class="reports-list">
                                `;
                                response.reports.forEach(report => {
                                    resultHtml += `
                                        <li class="report-item">
                                            <strong>Name:</strong> ${report.name} <br>
                                            <strong>Phone:</strong> ${report.phone} <br>
                                            <strong>Details:</strong> ${report.details} <br>
                                            <strong>Created At:</strong> ${report.created_at}
                                        </li>
                                    `;
                                });
                                resultHtml += '</ul></div>';
                            } else {
                                resultHtml += `
                                    <p class="text-center py-2">
                                        <i class="fas fa-check-circle text-success"></i>
                                        No reports found for this customer.
                                    </p>`;
                            }


                            $('#fraudModalBody').html(resultHtml);
                        } else {
                            $('#fraudModalBody').html(
                                '<div class="text-danger text-center">No data found for this phone number.</div>'
                            );
                        }
                    },
                    error: function(xhr) {
                        $('#fraudModalBody').html(
                            '<div class="text-danger text-center">Error fetching data.</div>'
                        );
                    }
                });
            });
        });

        let lastThanaValue1 = null;
        let lastThanaValue2 = null;

        // Handler for thanaSelect (with citySelect -> newAddress)
        $('#thanaSelect').on('change', function() {
            const currentValue = $(this).val();

            if (!currentValue || currentValue === lastThanaValue1) return;

            lastThanaValue1 = currentValue;

            const cityName = $('#citySelect option:selected').text().trim().replace(/\s+/g, ' ');
            const thanaName = $('#thanaSelect option:selected').text().trim().replace(/\s+/g, ' ');
            const formattedAddress = `City: ${cityName},\nThana: ${thanaName}`;

            $('#newAddress').val(formattedAddress);
        });

        // Handler for thanaSelect2Create (with citySelect2Create -> address)
        $('#thanaSelect2Create').on('change', function() {
            const currentValue = $(this).val();

            if (!currentValue || currentValue === lastThanaValue2) return;

            lastThanaValue2 = currentValue;

            const cityName = $('#citySelect2Create option:selected').text().trim().replace(/\s+/g, ' ');
            const thanaName = $('#thanaSelect2Create option:selected').text().trim().replace(/\s+/g, ' ');
            const formattedAddress = `City: ${cityName},\nThana: ${thanaName}`;

            $('#address').val(formattedAddress);
        });

        function togglePaymentFields() {
            const payment = parseFloat($('#payment').val()) || 0;
            const dueChecked = $('#due_button').is(':checked');
            const courierChecked = $('#courier').is(':checked');

            // If courier (Steadfast) is checked
            if (courierChecked) {
                $('#payment').prop('readonly', false);
                $('#payment_by').prop('disabled', true).val('').trigger('change').prop('required', false);
                $('#due_button').prop('disabled', false);
                return;
            }

            // Normal logic when courier is NOT checked
            if (dueChecked) {
                $('#payment').prop('readonly', true).val('');
                $('#payment_by').prop('disabled', true).val('').trigger('change').prop('required', false);
            } else {
                $('#payment').prop('readonly', false);
                if (payment > 0) {
                    $('#payment_by').prop('disabled', false).prop('required', true);
                    $('#due_button').prop('disabled', true).prop('checked', false);
                } else {
                    $('#payment_by').prop('disabled', true).val('').trigger('change').prop('required', false);
                    $('#due_button').prop('disabled', false);
                }
            }

            if ($('#payment_by').hasClass('select2-hidden-accessible')) {
                $('#payment_by').select2('destroy');
            }
            $('#payment_by').select2();
        }

        // Initial load
        $(document).ready(function() {
            togglePaymentFields();

            // Bind events with debounce for better performance
            let paymentTimer;
            $('#payment').on('input', function() {
                clearTimeout(paymentTimer);
                paymentTimer = setTimeout(togglePaymentFields, 100);
            });

            $('#due_button').on('change', function() {
                togglePaymentFields();
                // Reset payment field when due button changes
                if ($(this).is(':checked')) {
                    $('#payment').val(0);
                }
            });
        });
    </script>
@endpush
