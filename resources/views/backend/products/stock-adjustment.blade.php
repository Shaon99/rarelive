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
                            <form action="{{ route('admin.stock.adjust') }}" method="POST" class="needs-validation"
                                novalidate>
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="product_id" class="form-label">Product</label>
                                            <select name="product_id" class="form-select form-control select2"
                                                id="product_id">
                                                <option value="" disabled selected>-- Select Product --</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}{{ $product->code }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ __('Please select a product') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="warehouse_id" class="form-label">Branch</label>
                                            <select name="warehouse_id" id="warehouse_id"
                                                data-minimum-results-for-search="Infinity" class="form-control select2"
                                                required="">
                                                <option value="" disabled selected>-- Select Branch --</option>
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ __('Please select a warehouse') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Adjustment Type</label>
                                            <select name="type" id="type" class="form-control select2" required
                                                data-minimum-results-for-search="Infinity">
                                                <option value="" disabled selected>-- Select Type --</option>
                                                <option value="initial">Initial Stock</option>
                                                <option value="damage">Damage Stock</option>
                                                <option value="update">Add Without Purchase</option>
                                            </select>

                                            <div class="invalid-feedback">
                                                {{ __('Please select adjustment type') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" name="quantity" id="quantity" class="form-control"
                                                required="" min="1" placeholder="Enter quantity">
                                            <div class="invalid-feedback">
                                                {{ __('Please enter quantity') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="unit_price" class="form-label">Purchase Unit Price
                                                (optional)</label>
                                            <input type="number" step="0.01" name="unit_price" id="unit_price"
                                                class="form-control" placeholder="Enter purchases unit price">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="unit_price" class="form-label">Sale Unit Price (optional)</label>
                                            <input type="number" step="0.01" name="sale_price" id="sale_price"
                                                class="form-control" placeholder="Enter sale unit price">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="reason" class="form-label">Reason (optional)</label>
                                            <textarea name="reason" id="reason" class="form-control" rows="1" placeholder="Optional for damage stock"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary"
                                        type="submit">{{ __('Save Stock Adjustment') }}</button>
                                </div>
                            </form>

                            <div class="mt-0 mb-4">
                                <div class="card-header px-0">
                                    <h4 class="mb-0">Adjustment History</h4>
                                </div>
                                <div class="card-body px-0">
                                    <div class="row mb-2 flex-row-reverse">
                                        <div class="col-md-3">
                                            <label class="control-label">{{ __('Filter by Type') }}</label>
                                            <select name="type" class="form-control selectric" id="typeFilter"
                                                required>
                                                <option value="">All</option>
                                                <option value="initial">Initial Stock</option>
                                                <option value="damage">Damage Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="table-responsive" id="adjustments-table">
                                        @include('backend.products.adjustments-table')
                                    </div>
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
        $(document).ready(function() {
            $('#product_id').select2({
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

            $('#typeFilter').on('change', function() {
                let type = $(this).val();
                $.ajax({
                    url: '{{ route('admin.stock.adjust.form') }}',
                    method: 'GET',
                    data: {
                        type: type
                    },
                    beforeSend: function() {
                        $('#loading-overlay').show();
                    },
                    complete: function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#adjustments-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            });

            $(document).on('click', '#adjustments-table .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let page = new URL(url).searchParams.get('page');
                let type = $('#type').val();

                $.ajax({
                    url: url,
                    data: {
                        type: type,
                        page: page
                    },
                    beforeSend: function() {
                        $('#loading-overlay').show();
                    },
                    complete: function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#adjustments-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush
