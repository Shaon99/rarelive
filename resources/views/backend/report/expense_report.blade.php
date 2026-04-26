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
                                    <select name="products[]" id="category" data-placeholder="Select category..."
                                        class="form-control select2" multiple>
                                        @foreach ($expenseCategory as $id => $name)
                                            <option value="{{ $id }}">
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>                                
                                <div class="col-md-3 mb-2">
                                    <input type="text" class="form-control" id="search"
                                        placeholder="Search with amount" />
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

                                <button type="button" class="btn btn-success btn-sm" id="downloadReportButton">
                                    <i class="fa fa-download mr-1" aria-hidden="true" id="downloadIcon"></i> Download csv
                                </button>
                            </div>

                            <div class="table-responsive border-top" id="expense-report-table">
                                @include('backend.report.expense_report_table')
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
                        $('#expense-report-table').html(response.expenseReports);
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
            $(document).on('click', '#expense-report-table .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const page = new URL(url).searchParams.get('page'); // Get the page number from the URL

                const filters = getFilters(page); // Get filters and include the page number
                makeAjaxRequest(filters, "{{ route('admin.expense.report') }}");
            });

            // Get all filter values
            function getFilters(page = 1) {
                return {
                    search: $('#search').val(),
                    category: $('#category').val(),
                    date_range: $('#dateRangePicker').val(),
                    page: page,
                    per_page: $('#itemsPerPage').val(),
                };
            }

            // Handle items per page change
            $('#itemsPerPage').on('change', function() {
                const filters = getFilters(); // Get filters and include the updated per_page value
                makeAjaxRequest(filters, "{{ route('admin.expense.report') }}");
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
                    return; // Exit early if no filters are applied
                }

                // Clear any existing alerts
                $('#alertContainer').empty();

                // Make the AJAX request with the filters
                makeAjaxRequest(filters, "{{ route('admin.expense.report') }}", $btn, "Filter",
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
                makeAjaxRequest(getFilters(), "{{ route('admin.expense.report') }}", $btn, "Reset",
                    "Please wait...");
            });

            // Reset all filters
            function resetFilters() {
                $('#category').val([]).trigger('change');
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

            $('#downloadReportButton').on('click', function() {
                const $btn = $(this); // Get the button reference
                const $icon = $('#downloadIcon'); // Get the icon reference

                const filters = getFilters();

                const hasFilters = Object.entries(filters).some(([key, value]) =>
                    key !== 'per_page' && value && value.length > 0
                );

                if (!hasFilters) {
                    showAlert("Please select at least one filter option before applying.", "warning");
                    return;
                }

                $btn.prop('disabled', true);
                $('#progressContainer').show();
                $('#progressBar').css('width', '0%'); // Reset progress bar to 0%
                $('#progressBar').text('0%'); // Show 0% progress

                $icon.removeClass('fa-download').addClass('fa-spinner fa-spin');

                // Make the AJAX request to start the job
                $.ajax({
                    url: "{{ route('admin.download.expense.report') }}",
                    method: 'GET',
                    data: filters,
                    success: function(response) {
                        // Check batch ID to track progress
                        const batchId = response.batch_id;
                        if (!batchId) {
                            showAlert("Failed to start report generation. Please try again.",
                                "danger");
                            return;
                        }

                        // Poll for progress updates
                        const progressInterval = setInterval(function() {
                            $.ajax({
                                url: `/admin/batch/${batchId}/progress`,
                                method: 'GET',
                                success: function(progressReport) {
                                    const progress = progressReport
                                        .progress; // Correct the reference here
                                    $('#progressBar').css('width',
                                        progress + '%'
                                    ); // Update progress bar width
                                    $('#progressBar').text(progress +
                                        '%'); // Update progress text

                                    if (progress === 100) {
                                        clearInterval(
                                            progressInterval
                                        ); // Stop polling when progress reaches 100%
                                        // Change the icon back to download
                                        $icon.removeClass(
                                                'fa-spinner fa-spin')
                                            .addClass('fa-download');
                                        $btn.prop('disabled',
                                            false); // Enable the button

                                        // Automatically trigger the download using file_path
                                        const filePath = response
                                            .file_path; // Full file path from the server
                                        const fileName = response
                                            .file_name; // File name from the response

                                        // Create a temporary link element to trigger the download
                                        const downloadLink = document
                                            .createElement('a');
                                        downloadLink.href =
                                            filePath; // Use the full file path
                                        downloadLink.download =
                                            fileName; // Use the dynamic file name
                                        downloadLink
                                            .click(); // Trigger the download

                                    }
                                }
                            });
                        }, 2000); // Check every 2 seconds
                    },
                    error: function() {
                        $btn.prop('disabled', false);
                        showAlert("An error occurred. Please try again.", "danger");
                    }
                });
            });


        });
    </script>
@endpush
