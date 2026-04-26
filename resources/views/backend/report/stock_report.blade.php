@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <select name="product_id" class="form-select form-control select2" id="product_id">
                                    </select>
                                </div>
                            </div>
                            <div id="stock-report-table table-responsive">
                                @include('backend.report.stock_report_table')
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
                minimumInputLength: 1
            });

            // Load table on product select
            $('#product_id').on('change', function(e) {
                let product_id = $(this).val();
                $.ajax({
                    url: '{{ route('admin.stock.report') }}',
                    method: 'GET',
                    data: {
                        product_id: product_id
                    },
                    beforeSend: function() {
                        $('#loading-overlay').show();
                    },
                    complete: function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#stock-report-table').html(response.table);
                    },
                    error: function() {
                        alert('Something went wrong!');
                    }
                });
            });
        });
    </script>
@endpush
