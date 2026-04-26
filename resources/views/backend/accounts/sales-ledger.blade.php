@extends('backend.layout.master')
@push('style')
    <style>
        table:not(.table-sm):not(.table-md):not(.dataTable) td,
        .table:not(.table-sm):not(.table-md):not(.dataTable) th {
            height: 30px !important;
            font-size: 12px;
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
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dateRangePicker">Select Date Range:</label>
                                <input type="text" class="form-control" id="dateRangePicker"
                                    placeholder="Select date range" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="sales-ledger-table">
                        @include('backend.accounts.sales-ledger-table')
                    </div>

                    <div class="d-flex justify-content-end py-4">
                        <button class="btn btn-primary btn-icon icon-left d-none print-btn" onclick="printPage()"><i
                                class="fas fa-print"></i>
                            Print</button>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        $(document).ready(function() {
            // Initialize the date range picker (default: today only)
            var startDate = moment();
            var endDate = moment();

            $('#dateRangePicker').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                startDate: startDate,
                endDate: endDate,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#dateRangePicker').val(startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('YYYY-MM-DD'));
            fetchSalesLedger(startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD'));

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                fetchSalesLedger(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Reset to today's range on cancel
            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                var today = moment();
                picker.setStartDate(today);
                picker.setEndDate(today);
                $(this).val(today.format('YYYY-MM-DD') + ' - ' + today.format('YYYY-MM-DD'));
                fetchSalesLedger(today.format('YYYY-MM-DD'), today.format('YYYY-MM-DD'));
            });

            $('#customer').on('change', function() {
                var dates = $('#dateRangePicker').val().split(' - ');
                var startDate = dates[0] || '';
                var endDate = dates[1] || '';
                fetchSalesLedger(startDate, endDate);
            });

            // Function to fetch the sales ledger via AJAX
            function fetchSalesLedger(startDate = '', endDate = '') {
                $.ajax({
                    url: '{{ route('admin.sales.ledger') }}',
                    type: 'GET',
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                    },
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#sales-ledger-table').html(response);
                        $('.print-btn').removeClass('d-none');
                    },
                    error: function(xhr) {
                        console.error('Error fetching sales ledger:', xhr.responseText);
                    }
                });
            }
        });

        function printPage() {
            var printContent = document.getElementById('sales-ledger-table').innerHTML;
            var iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';

            // Append iframe to the body
            document.body.appendChild(iframe);

            // Write content and styles to the iframe
            var doc = iframe.contentWindow.document;
            doc.open();
            doc.write('<html><head><title>Sales Ledger</title>');

            // Copy styles from the main page
            var styles = '';
            var styleSheets = document.styleSheets;
            for (var i = 0; i < styleSheets.length; i++) {
                try {
                    var rules = styleSheets[i].cssRules || styleSheets[i].rules;
                    for (var j = 0; j < rules.length; j++) {
                        styles += rules[j].cssText;
                    }
                } catch (e) {}
            }
            doc.write('<style>' + styles + '</style>');
            doc.write('</head><body>');
            doc.write(printContent);
            doc.write('</body></html>');
            doc.close();
            iframe.contentWindow.print();
            iframe.contentWindow.onafterprint = function() {
                document.body.removeChild(iframe);
            };
        }
    </script>
@endpush
