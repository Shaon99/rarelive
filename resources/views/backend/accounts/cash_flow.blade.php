@extends('backend.layout.master')
@push('style')
    <style>
        .border-r {
            border-right: 1px solid #bbbbbb;
        }

        .dotted-border {
            border-bottom: 1px dotted #000;
            padding: 5px 0;
        }

        .indent {
            padding-left: 20px;
        }

        .amount {
            float: right;
            padding-right: 15px;
        }

        .total-box {
            border: 1px solid #000;
            padding: 3px 8px;
            margin-top: 5px;
        }

        table:not(.table-sm):not(.table-md):not(.dataTable) td,
        .table:not(.table-sm):not(.table-md):not(.dataTable) th {
            height: 35px !important;
            font-size: 14px;
        }

        .no-record {
            color: #000;
            padding-left: 20px;
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
                    <div id="cash-flow">
                        @include('backend.accounts.cash-flow-table')
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
            var startDate = moment().startOf('day');
            var endDate = moment().endOf('day');

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

            // Set the initial value in the input field
            $('#dateRangePicker').val(startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('YYYY-MM-DD'));

            // Fetch initial sales ledger data
            fetchCashFlow(startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD'));

            // Update input value on date selection
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                fetchCashFlow(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Clear input value on cancel
            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                fetchCashFlow();
            });

            // Function to fetch the sales ledger via AJAX
            function fetchCashFlow(startDate = '', endDate = '') {
                $.ajax({
                    url: '{{ route('admin.cash.flow') }}',
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
                        $('#cash-flow').html(response);
                        $('.print-btn').removeClass('d-none');
                    },
                    error: function(xhr) {
                        console.error('Error fetching cash flow:', xhr.responseText);
                    }
                });
            }
        });

        function printPage() {
            var printContent = document.getElementById('cash-flow').innerHTML;
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
            doc.write('<html><head><title>Cash Flow</title>');

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

            // Optionally remove the iframe after printing
            iframe.contentWindow.onafterprint = function() {
                document.body.removeChild(iframe);
            };
        }
    </script>
@endpush
