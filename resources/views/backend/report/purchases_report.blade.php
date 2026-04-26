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
                    <div id="alertContainer"></div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" class="form-control" id="dateRangePicker"
                                        placeholder="Select date range" autocomplete="off" />
                                </div>

                                <div class="col-md-3 mb-2">
                                    <select class="form-control select2" data-placeholder="Select payment status..."
                                        name="payment_status[]" id="payment_status" multiple>
                                        <option value="2">{{ __('Due') }}</option>
                                        <option value="1">{{ __('Paid') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <input type="text" class="form-control" id="search"
                                        placeholder="Search with supplier name, phone, invoice no" />
                                </div>

                                <div class="col-md-2 mb-2">
                                    <div class="d-flex justify-content-end mx-0">
                                        <button id="applyFilter" class="btn btn-primary">
                                            <span id="filter">{{ __('Filter') }}</span>
                                        </button>

                                        <button id="resetFilter" title="Everything will be reset"
                                            class="btn btn-danger ml-2">
                                            <span id="reset">{{ __('Reset') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between py-4">
                                        <div class="d-flex align-items-center">
                                            <select class="form-control selectric" id="itemsPerPage">
                                                <option value="10">10</option>
                                                <option value="20">20</option>
                                                <option value="20" selected>25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="table-responsive border-top" id="purchases-report-table">
                                        @include('backend.report.purchases_report_table')
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
        'use strict';
        $(document).ready(function() {
            // Initialize Select2 for dropdowns
            $(".select2").each(function() {
                $(this).select2({
                    placeholder: $(this).data("placeholder")
                });
            });

            // Initialize Date Range Picker
            $('#dateRangePicker').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            // Handle date range selection
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
            }).on('cancel.daterangepicker', function() {
                $(this).val('');
            });

            // Function to make AJAX requests
            function makeAjaxRequest(filters, url, $btn = null, originalText = null, loadingText = null) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: filters,
                    beforeSend: function() {
                        toggleButtonState($btn, true, originalText, loadingText);
                    },
                    complete: function() {
                        toggleButtonState($btn, false, originalText, loadingText);
                    },
                    success: function(response) {
                        $('#purchases-report-table').html(response.purchases);
                        // Update the total grand amount
                        const grandTotal = parseFloat(response.grandTotal);
                        $('#total').text(grandTotal.toFixed(2));
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            }

            // Handle pagination links
            $(document).on('click', '#purchases-report-table .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const page = new URL(url).searchParams.get('page');

                const filters = getFilters(page);
                makeAjaxRequest(filters, "{{ route('admin.purchases.report') }}");
            });

            // Get all filter values
            function getFilters(page = 1) {
                return {
                    search: $('#search').val(),
                    date_range: $('#dateRangePicker').val(),
                    payment_status: $('#payment_status').val(),
                    page: page,
                    per_page: $('#itemsPerPage').val(),
                };
            }

            // Handle items per page change
            $('#itemsPerPage').on('change', function() {
                const filters = getFilters(); // Get filters and include the updated per_page value
                makeAjaxRequest(filters, "{{ route('admin.purchases.report') }}");
            });

            // Apply filter
            $('#applyFilter').on('click', function() {
                const $btn = $(this);
                const filters = getFilters();

                // Check if at least one filter has a value
                const hasFilters = Object.entries(filters).some(([key, value]) =>
                    key !== 'per_page' && value && value.length > 0
                );

                if (!hasFilters) {
                    showAlert("Please select at least one filter option before applying.", "warning");
                    return;
                }
                $('#alertContainer').empty();
                makeAjaxRequest(filters, "{{ route('admin.purchases.report') }}", $btn, "Filter","Please wait...");
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                const $btn = $(this);
                // Check if any filter has a value
                const filters = getFilters();
                // Check if at least one filter has a value
                const hasFilters = Object.entries(filters).some(([key, value]) =>
                    key !== 'per_page' && value && value.length > 0
                );
                if (!hasFilters) {
                    showAlert("There are no active filters to reset.", "warning");
                    return;
                }
                $('#alertContainer').empty();
                resetFilters();
                makeAjaxRequest(getFilters(), "{{ route('admin.purchases.report') }}", $btn, "Reset", "Please wait...");
            });


            // Reset all filters
            function resetFilters() {
                $('#dateRangePicker').val('').trigger('cancel.daterangepicker');
                $('#payment_status').val([]).trigger('change');
                $('#search').val('');
            }

            function showAlert(message, type = "warning") {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert" id="autoCloseAlert">
                        <strong>${type === "success" ? "Success" : "Warning"}:</strong> ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
            }

            function toggleButtonState($button, isLoading, originalText, loadingText) {
                if ($button) {
                    $button.prop('disabled', isLoading).text(isLoading ? loadingText : originalText);
                }
                $('#loading-overlay').toggle(isLoading);
            }
        });
    </script>
@endpush
