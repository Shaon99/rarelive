@extends('backend.layout.master')
@push('style')
    <style>
        table th,
        table td {
            min-width: 100px;
            /* You can adjust this value */
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
                            <form action="{{ route('admin.purchases.store') }}" method="POST" class="needs-validation"
                                novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="mb-3 col-md-4 col-sm-12 col-12">
                                        <label class="label" for="code">{{ __('Reference No') }}</label>
                                        <input type="text" name="reference_no" class="form-control"
                                            value="{{ old('reference_no') }}" placeholder="{{ __('Enter reference no') }}">
                                    </div>

                                    <div class="mb-3 col-md-4 col-sm-12 col-12">
                                        <label>{{ __('Invoice No') }}</label>
                                        <input type="text" class="form-control" name="invoice_no" readonly
                                            value="{{ $voucherNo }}" placeholder="Enter invoice no">
                                    </div>

                                    <div class="mb-3 col-md-4 col-12">
                                        <label>{{ __('Branch') }}</label>
                                        <div class="d-flex">
                                            <select class="form-control select2 flex-grow-1"
                                                data-minimum-results-for-search="Infinity" name="warehouse" required=""z>
                                                <option value="" selected disabled>{{ __('Select branch...') }}
                                                </option>
                                                @forelse ($warehouses as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ $loop->first ? 'selected' : '' }}>{{ $item->name }}</option>
                                                @empty
                                                    <option disabled selected>No branch available</option>
                                                @endforelse
                                            </select>
                                            <button type="button" data-href="{{ route('admin.warehouse.store') }}"
                                                data-name="Warehouse" class="btn btn-primary ml-2 create">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3 col-md-4 col-sm-12 col-12">
                                        <label>{{ __('Supplier') }}</label>
                                        <select class="form-control select2" name="supplier" required="">
                                            <option value="" selected disabled>{{ __('Select supplier...') }}
                                            </option>
                                            @forelse ($suppliers as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @empty
                                                <option disabled selected>No supplier available</option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('supplier can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="mb-3 col-md-4 col-sm-12 col-12">
                                        <label>{{ __('Purchase a Date') }}</label>
                                        <input type="text" name="purchase_date" class="form-control datepicker">
                                    </div>

                                    <div class="mb-3 col-md-4 col-sm-12 col-12">
                                        <label>{{ __('Select Product') }}</label>
                                        <select class="form-control select2" name="product" id="product">
                                            <option value="" selected disabled>{{ __('Select product...') }}
                                            </option>
                                            @forelse ($products as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @empty
                                                <option disabled selected>No products available</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered order-list bg-white" id="invoice">
                                                <thead class="info">
                                                    <tr>
                                                        <th>{{ __('Product Name') }}</th>
                                                        <th>{{ __('Quantity') }}</th>
                                                        <th>{{ __('Purchases Unit Price') }}</th>
                                                        <th>{{ __('Sales Unit Price') }}</th>
                                                        <th>{{ __('Sub Total') }}</th>
                                                        <th>{{ __('Delete') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="d-none mt-2" id="preloader">
                                                        <th colspan="12">
                                                            <div class="d-flex justify-content-center">
                                                                <div class="spinner-border" role="status">
                                                                    <span class="visually-hidden"></span>
                                                                </div>
                                                            </div>
                                                        </th>
                                                    </tr>
                                                </tbody>

                                                <tfoot>
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th>{{ __('Items') }} <span class="text-center" id="total_item">0
                                                            </span></th>

                                                        <input type="text" hidden id="total_qty" name="total_qty">

                                                        <th>{{ __('Subtotal') }} <span class="text-center" id="total_sub">0
                                                                {{ @$general->site_currency }}</span></th>
                                                        <input type="text" hidden id="sub_totals" name="subtotals">
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-md-2 form-group">
                                        <label for="discount" class="control-label">{{ __('Discount') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" min="0" step="0.05"
                                                value="0" name="discount" id="discount" placeholder="Enter Discount">
                                            <select class="form-control selectric" name="discount_type" id="discount_type">
                                                <option value="fixed">{{ __('Flat') }}</option>
                                                <option value="percentage">{{ __('%') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group text-bold">
                                            <label for="shipping"
                                                class="control-label">{{ __('Shipping Charge') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" min="0" step="0.50"
                                                    value="0" name="shipping" id="shipping"
                                                    placeholder="Enter Shipping">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group  text-bold">
                                            <label for="payment" class="control-label">{{ __('Paying Amount') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" min="0" step="any"
                                                    value="0" name="payment" id="payment"
                                                    placeholder="Enter payment">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group text-bold">
                                            <label for="payment" class="control-label">{{ __('Due Amount') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" value="0"
                                                    name="duepayment" id="duepayment" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
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

                                    <div class="col-md-2">
                                        <div class="form-group text-bold">
                                            <label>{{ __('Payment Account') }}</label>
                                            <select class="form-control select2" data-minimum-results-for-search="Infinity" name="payment_by" id="payment_by"
                                                required="">
                                                <option value="" selected disabled>{{ __('Select Payment Method') }}
                                                </option>
                                                @forelse ($payment_account as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }} @if ($item->account_number)
                                                            (AC: {{ $item->account_number }})
                                                        @endif
                                                    </option>
                                                @empty
                                                    <option disabled selected>No payment method available</option>
                                                @endforelse
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ __('Please Add a payment account') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group text-bold">
                                            <label for="note" class="control-label">{{ __('Note') }}</label>
                                            <div class="input-group">
                                                <textarea name="note" class="form-control" placeholder="Type here..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 py-4">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-responsivebg-light">
                                                <tfoot>
                                                    <tr>
                                                        <th> {{ __('Items') }} <span class="pull-right"
                                                                id="total_items">0</span></th>
                                                        <th> {{ __('Total') }} <span class="pull-right"
                                                                id="total_value">0
                                                            </span> {{ @$general->site_currency }}</th>
                                                        <th> {{ __('Discount') }} <span class="pull-right"
                                                                id="total_discount">0
                                                            </span> {{ @$general->site_currency }}</th>

                                                        <th> {{ __('Shipping') }} <span class="pull-right"
                                                                id="total_shipping">0
                                                            </span> {{ @$general->site_currency }}</th>
                                                        <th> {{ __('Grand Total') }} <span class="pull-right"
                                                                id="grand_total">0
                                                            </span> {{ @$general->site_currency }}</th>
                                                        <th> {{ __('Due Amount') }} <span class="pull-right"
                                                                id="dueamount">0 </span>
                                                            {{ @$general->site_currency }}
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <input type="hidden" class="sub_total" name="sub_total" value="" />
                                <input type="hidden" class="dis_total" name="dis_total" value="" />
                                <input type="hidden" class="grand_total" name="grand_total" value="" />
                                <div class="d-flex justify-content-center">
                                    <button class="btn btn-primary w-25"
                                        type="submit">{{ __('Create Purchases') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('script')
    <script src="{{ asset('assets/admin/js/purchase.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#product').select2({
                placeholder: '--Search Product with product name or code--',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.search.product') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
            });

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
                        newDataAppend(data);                        
                        calculateTotal();
                    }
                })
            });

            function togglePaymentFields() {
                const payment = parseFloat($('#payment').val()) || 0;
                const dueChecked = $('#due_button').is(':checked');

                if (dueChecked) {
                    $('#payment').prop('readonly', true).val('');
                    $('#payment_by').prop('disabled', true).val('').trigger('change');
                } else {
                    $('#payment').prop('readonly', false);
                    if (payment > 0) {
                        $('#payment_by').prop('disabled', false);
                        $('#due_button').prop('disabled', true).prop('checked', false);
                    } else {
                        $('#payment_by').prop('disabled', true).val('').trigger('change');
                        $('#due_button').prop('disabled', false);
                    }
                }
            }
            // Initial load
            $(document).ready(function() {
                togglePaymentFields();
                $('#payment').on('input', togglePaymentFields);
                $('#due_button').on('change', togglePaymentFields);
            });
        });
    </script>
@endpush
