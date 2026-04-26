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
                    <div class="form-group">
                        <label for="dateRangePicker">Select Date Range:</label>
                        <input type="text" class="form-control w-25" id="dateRangePicker" placeholder="Select date range"
                            autocomplete="off" />
                    </div>
                    <div class="table-responsive" id="expense-ledger-table">
                        @include('backend.accounts.expenses-ledger-table')
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
            // Initialize the date range picker
            var startDate = moment().startOf('month'); // Start of current month
            var endDate = moment().endOf('month'); // End of current month

            $('#dateRangePicker').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                startDate: startDate,
                endDate: endDate,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD' // Format for dates
                }
            });

            // Set the initial value in the input field
            $('#dateRangePicker').val(startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('YYYY-MM-DD'));

            // Fetch initial sales ledger data
            fetchExpenseLedger(startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD'));

            // Update input value on date selection
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                fetchExpenseLedger(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Clear input value on cancel
            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                fetchExpenseLedger(); // Fetch all data with no date range
            });

            // Function to fetch the Expense ledger via AJAX
            function fetchExpenseLedger(startDate = '', endDate = '') {
                $.ajax({
                    url: '{{ route('admin.expense.ledger') }}', // Update this route as needed
                    type: 'GET',
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        // Update the ledger table with the response
                        $('#expense-ledger-table').html(response);
                        $('.print-btn').removeClass('d-none');
                    },
                    error: function(xhr) {
                        console.error('Error fetching expense ledger:', xhr.responseText);
                    }
                });
            }
        });

        function printPage() {
            var printContent = document.getElementById('expense-ledger-table').innerHTML;
            // Create an iframe
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
            doc.write('<html><head><title>Expense Ledger</title>');

            // Copy styles from the main page
            var styles = '';
            var styleSheets = document.styleSheets;
            for (var i = 0; i < styleSheets.length; i++) {
                try {
                    var rules = styleSheets[i].cssRules || styleSheets[i].rules;
                    for (var j = 0; j < rules.length; j++) {
                        styles += rules[j].cssText;
                    }
                } catch (e) {
                    // Handle cross-origin style sheets or errors in fetching styles
                }
            }
            doc.write('<style>' + styles + '</style>'); // Add styles to the iframe
            doc.write('</head><body>');
            doc.write(printContent); // Add the header and content to be printed
            doc.write('</body></html>');
            doc.close();

            // Print the iframe content
            iframe.contentWindow.print();

            // Optionally remove the iframe after printing
            iframe.contentWindow.onafterprint = function() {
                document.body.removeChild(iframe);
            };
        }
    </script>
@endpush
