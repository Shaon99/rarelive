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
                        @if($type == 'supplier')
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="supplier">Select Supplier:</label>
                                <select class="form-control select2" id="supplier" name="supplier">
                                    <option value="">All </option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="table-responsive" id="purchases-ledger-table">
                        @include('backend.accounts.purchases-ledger-table')
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
            var startDate = moment().startOf('month');
            var endDate = moment().endOf('month');

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
            fetchPurchasesLedger(startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD'));

            // Update input value on date selection
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                fetchPurchasesLedger(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Clear input value on cancel
            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                fetchPurchasesLedger();
            });

            // Handle supplier filter change
            $('#supplier').on('change', function() {
                var dates = $('#dateRangePicker').val().split(' - ');
                var startDate = dates[0] || '';
                var endDate = dates[1] || '';
                var supplierId = $(this).val();

                fetchPurchasesLedger(startDate, endDate, supplierId);
            });

            // Function to fetch the purchases ledger via AJAX
            function fetchPurchasesLedger(startDate = '', endDate = '', supplierId = '') {
                $.ajax({
                    url: '{{ route('admin.purchases.ledger') }}',
                    type: 'GET',
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        supplier_id: supplierId
                    },
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#purchases-ledger-table').html(response);
                        $('.print-btn').removeClass('d-none');
                    },
                    error: function(xhr) {
                        console.error('Error fetching purchases ledger:', xhr.responseText);
                    }
                });
            }
        });

        function printPage() {
            var printContent = document.getElementById('purchases-ledger-table').innerHTML;
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
            doc.write('<html><head><title>Purchases Ledger</title>');

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
