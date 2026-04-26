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
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control searchInput" id="searchInput"
                                            placeholder="Search by product name or invoice no...">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive mt-4" id="sale-return-adjustments-table">
                                @include('backend.sales.sale-return-adjustments-table')
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
            $(document).on('click', '#sale-return-adjustments-table .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let page = new URL(url).searchParams.get('page');
                let search = $('#searchInput').val();
                $('#sale-return-adjustments-table').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Searching...</p>
                    </div>
                `);
                $.ajax({
                    url: url,
                    data: {
                        page: page,
                        search: search
                    },
                    success: function(response) {
                        $('#sale-return-adjustments-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            });

            // Debounced search handler
            let searchTimeout;
            $(document).on('keyup', '#searchInput', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });

            // Optimized search function
            function performSearch() {
                const $resultsTable = $('#sale-return-adjustments-table');
                const search = $('#searchInput').val().trim();

                // Loading template
                const loadingTemplate = `
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Searching...</p>
                    </div>
                `;

                $resultsTable.html(loadingTemplate);

                $.ajax({
                    url: '{{ route('admin.returnList.index') }}',
                    method: 'GET',
                    data: {
                        search
                    },
                    cache: true,
                    success: response => $resultsTable.html(response),
                    error: () => $resultsTable.html(`
                        <div class="alert alert-danger text-center" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Search failed. Please try again.
                        </div>
                    `)
                });
            }
        });
    </script>
@endpush