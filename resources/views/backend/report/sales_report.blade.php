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
                                    <select name="products[]" id="products" data-placeholder="Select products..."
                                        class="form-control select2" multiple>
                                        @foreach ($products as $id => $name)
                                            <option value="{{ $id }}">
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <select name="created_by[]" id="created_by" data-placeholder="Select created by..."
                                        class="form-control select2" multiple>
                                        @foreach ($adminUsers as $id => $name)
                                            <option value="{{ $id }}">
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <select class="form-control select2" data-placeholder="Select status..."
                                        name="s_status[]" id="s_status" multiple>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-control select2" data-placeholder="Select shipping status..."
                                        name="courier_status[]" id="c_status" multiple>
                                        <option value="in_review">In-Review</option>
                                        <option value="pending">Pending</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="partial_delivered">Partial-Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                @if ($general->pos_platform_on_off == 1)
                                    <div class="col-md-3 mb-2">
                                        <select class="form-control select2" data-placeholder="Select platform..."
                                            name="platform[]" id="platform" multiple>
                                            <option value="facebook">Facebook</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="others">Others</option>
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-3 mb-2">
                                    <select class="form-control select2" data-placeholder="Select payment method..."
                                        name="payment_by[]" id="payment_by" multiple>
                                        @forelse ($paymentMethods as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <select class="form-control select2" data-placeholder="Filter by location.."
                                        name="location[]" id="location" multiple>
                                        @forelse ($districts as $item)
                                            <option value="{{ $item['name'] }}">
                                                {{ $item['name'] }}
                                                {{ isset($item['bn_name']) && $item['bn_name'] ? '(' . $item['bn_name'] . ')' : '' }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <select class="form-control select2" data-placeholder="Select payment status..."
                                        name="payment_status[]" id="payment_status" multiple>
                                        <option value="2">{{ __('Due') }}</option>
                                        <option value="1">{{ __('Paid') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <input type="text" class="form-control" id="search"
                                        placeholder="Search with customer info, invoice, consignment id, tracking code" />
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex mx-0">
                                        <button id="applyFilter" class="btn btn-primary">
                                            <span id="filter">{{ __('Filter') }}</span>
                                        </button>

                                        <button id="resetFilter" title="Everything will be reset"
                                            class="btn btn-danger ml-2">
                                            <span id="reset">{{ __('Reset') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
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

                                <div id="progressContainer" style="display: none;">
                                    <div class="f-12">Generating Report...</div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                            aria-valuemax="100" id="progressBar">
                                            0%
                                        </div>
                                    </div>
                                </div>
                                <!-- Hidden Form for CSV Download -->
                                <form id="csvDownloadForm" method="GET"
                                    action="{{ route('admin.download.sales.report') }}" class="d-none">
                                    <input type="hidden" name="products">
                                    <input type="hidden" name="created_by">
                                    <input type="hidden" name="s_status">
                                    <input type="hidden" name="courier_status">
                                    <input type="hidden" name="platform">
                                    <input type="hidden" name="payment_by">
                                    <input type="hidden" name="payment_status">
                                    <input type="hidden" name="date_range">
                                    <input type="hidden" name="search">
                                </form>

                                <button type="button" onclick="downloadCsv()" class="btn btn-success btn-sm"
                                    id="downloadReportButton">
                                    <i class="fa fa-download mr-1" aria-hidden="true" id="downloadIcon"></i>
                                    <span id="downloadText">Download CSV</span>
                                </button>
                            </div>

                            <div class="table-responsive border-top" id="sales-report-table">
                                @include('backend.report.sales_report_table')
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
                        $('#sales-report-table').html(response.salesReports);
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
            $(document).on('click', '#sales-report-table .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const page = new URL(url).searchParams.get('page'); // Get the page number from the URL

                const filters = getFilters(page); // Get filters and include the page number
                makeAjaxRequest(filters, "{{ route('admin.sales.report') }}");
            });

            // Get all filter values
            function getFilters(page = 1) {
                return {
                    search: $('#search').val(),
                    products: $('#products').val(),
                    created_by: $('#created_by').val(),
                    s_status: $('#s_status').val(),
                    courier_status: $('#c_status').val(),
                    platform: $('#platform').val(),
                    date_range: $('#dateRangePicker').val(),
                    payment_by: $('#payment_by').val(),
                    payment_status: $('#payment_status').val(),
                    location: $('#location').val(),
                    page: page,
                    per_page: $('#itemsPerPage').val(),
                };
            }

            // Handle items per page change
            $('#itemsPerPage').on('change', function() {
                const filters = getFilters();
                makeAjaxRequest(filters, "{{ route('admin.sales.report') }}");
            });

            // Apply filter
            $('#applyFilter').on('click', function() {
                const $btn = $(this); // Get the button reference

                const filters = getFilters();

                // Check if at least one filter has a value
                const hasFilters = Object.entries(filters).some(([key, value]) =>
                    key !== 'per_page' && value && value.length > 0
                );

                if (!hasFilters) {
                    showAlert("Please select at least one filter option before applying.", "warning");
                    return;
                }

                // Clear any existing alerts
                $('#alertContainer').empty();

                // Make the AJAX request with the filters
                makeAjaxRequest(filters, "{{ route('admin.sales.report') }}", $btn, "Filter",
                    "Please wait...");
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
                    return; // Prevent server call if no filters are applied
                }

                // Clear any existing alerts
                $('#alertContainer').empty();

                // Reset all filters
                resetFilters();

                // Make an AJAX request to fetch default data
                makeAjaxRequest(getFilters(), "{{ route('admin.sales.report') }}", $btn, "Reset",
                    "Please wait...");
            });


            // Reset all filters
            function resetFilters() {
                $('#products').val([]).trigger('change');
                $('#created_by').val([]).trigger('change');
                $('#s_status').val([]).trigger('change');
                $('#c_status').val([]).trigger('change');
                $('#platform').val([]).trigger('change');
                $('#dateRangePicker').val('').trigger('cancel.daterangepicker');
                $('#payment_by').val([]).trigger('change');
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

        function downloadCsv() {
            const button = document.getElementById('downloadReportButton');
            const icon = document.getElementById('downloadIcon');
            const text = document.getElementById('downloadText');
            const form = document.getElementById('csvDownloadForm');

            // Show loading state
            icon.classList.remove('fa-download');
            icon.classList.add('fa-spinner', 'fa-spin');
            text.textContent = 'Generating...';
            button.disabled = true;

            // Populate filters from your form inputs (update selectors as needed)
            form.elements['products'].value = document.querySelector('#product_filter')?.value || '';
            form.elements['created_by'].value = document.querySelector('#created_by_filter')?.value || '';
            form.elements['s_status'].value = document.querySelector('#status_filter')?.value || '';
            form.elements['courier_status'].value = document.querySelector('#courier_status_filter')?.value || '';
            form.elements['platform'].value = document.querySelector('#platform_filter')?.value || '';
            form.elements['payment_by'].value = document.querySelector('#payment_by_filter')?.value || '';
            form.elements['payment_status'].value = document.querySelector('#payment_status_filter')?.value || '';
            form.elements['date_range'].value = document.querySelector('#date_range_filter')?.value || '';
            form.elements['search'].value = document.querySelector('#search_input')?.value || '';
            form.submit();

            setTimeout(() => {
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-download');
                text.textContent = 'Download CSV';
                button.disabled = false;
            }, 1000);
        }
    </script>
@endpush
