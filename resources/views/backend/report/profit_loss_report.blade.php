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
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label for="dateRangePicker">Select Date Range:</label>
                                    <input type="text" class="form-control" id="dateRangePicker"
                                        placeholder="Select date range" autocomplete="off" />
                                </div>
                            </div>
                            <div id="profit-loss-table" class="table-responsive">
                                @if (isset($salesTotal))
                                    @include('backend.report.profit_loss_report_table')
                                @else
                                    <div id="loading-overlay" class="loading-overlay">
                                        <div class="loading-overlay-text text-center">please wait...</div>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-primary btn-icon icon-left print-btn" onclick="printPage()"><i
                                        class="fas fa-print"></i>
                                    Print</button>
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
        'use strict'
        $(document).ready(function() {
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

            $('#dateRangePicker').val(startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('YYYY-MM-DD'));

            fetchCashFlow(startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD'));

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                fetchCashFlow(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                fetchCashFlow();
            });

            function fetchCashFlow(startDate = '', endDate = '') {
                $.ajax({
                    url: '{{ route('admin.report.profitLossReport') }}',
                    type: 'GET',
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    beforeSend: function() {
                        $('#loading-overlay').show();
                    },
                    complete: function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#profit-loss-table').html(response.html);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            }
        });

        function printPage() {
            var printContent = document.getElementById('profit-loss-table').innerHTML;
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
            doc.write('<html><head><title>Profit/Loss Report</title>');

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
