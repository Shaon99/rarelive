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
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <label for="model" class="control-label">{{ __('Filter by Model') }}</label>
                                    <select name="model" id="model" class="form-control select2">
                                        <option value="" selected disabled>-- Select Model --</option>
                                        <option value="">All</option>
                                        @foreach ($models as $model)
                                            <option value="{{ $model }}"
                                                {{ request('model') == $model ? 'selected' : '' }}>
                                                {{ class_basename($model) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Date Range Filter -->
                                <div class="col-md-5">
                                    <label for="date_range">{{ __('Date Range Filter') }}</label>
                                    <input type="text" id="date_range" class="form-control" name="date_range" />
                                </div>
                                <!-- Filter Button -->
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="button" id="recycle-bin-filters" class="btn btn-primary btn-block">
                                        <span id="filter">{{ __('Filter') }}</span>
                                        <span id="loading-spinner" class="spinner-border spinner-border-sm ml-2 d-none"
                                            role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <form id="deleteMultipleForm" action="{{ route('admin.recycle_bin.multiple.delete_forever') }}"
                                method="POST">
                                @csrf
                                <div class="py-2">
                                    <button type="submit" class="btn btn-danger btn-sm d-none" id="deleteSelected"
                                        disabled>
                                        <i class="fas fa-trash mr-1"></i> Delete Selected
                                    </button>
                                </div>

                                <div class="table-responsive" id="recycle-bin-table">
                                    @include('backend.recycle-bin.table')
                                </div>
                            </form>
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
            $(function() {
                $('input[name="date_range"]').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear'
                    }
                });

                $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate
                        .format('MM/DD/YYYY'));
                });

                $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            });

            //view modal dialog
            $(document).on('click', '.viewData', function(event) {
                const modal = $('#dataModal');
                let data = $(this).data('json');

                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }

                // Recursive function to format data
                function formatTable(data) {
                    let html = '<table class="table table-bordered">';
                    Object.keys(data).forEach((key) => {
                        const value = data[key];
                        html += '<tr>';
                        html += `<th>${key}</th>`;
                        if (Array.isArray(value)) {
                            // Show nested table for arrays (like purchaseProducts)
                            html += '<td>';
                            value.forEach((item, index) => {
                                html +=
                                    `<div class="mb-2"><strong>Item ${index + 1}</strong>${formatTable(item)}</div>`;
                            });
                            html += '</td>';
                        } else if (typeof value === 'object' && value !== null) {
                            // Show nested table for objects
                            html += `<td>${formatTable(value)}</td>`;
                        } else {
                            html += `<td>${value}</td>`;
                        }
                        html += '</tr>';
                    });
                    html += '</table>';
                    return html;
                }

                // Render the data
                const formattedData = formatTable(data);
                $('#modalData').html(formattedData);
                modal.modal('show');
            });


            $(document).on('click', '#recycle-bin-filters', function(event) {
                let dateRange = $('#date_range').val();
                let model = $('#model').val();
                var $btn = $(this);

                $.ajax({
                    url: '{{ route('admin.recycle_bin.index') }}',
                    method: 'GET',
                    data: {
                        date_range: dateRange,
                        model: model
                    },
                    beforeSend: function() {
                        $('#filter').text('Please wait...');
                        $btn.prop('disabled', true);
                        $('#loading-spinner').removeClass('d-none');
                        $('#loading-overlay').show();
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $('#filter').text('Filter');
                        $('#loading-spinner').addClass('d-none');
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#recycle-bin-table').html(response);
                    }
                });
            });

            // Handle pagination links
            $(document).on('click', '#recycle-bin-table .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let page = new URL(url).searchParams.get('page'); // Get the page number from the URL
                const filters = {
                    date_range: $('#date_range').val(),
                    model: $('#model').val(),
                    page: page
                };
                $.ajax({
                    url: url,
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#activity-table').html(response);
                        rebindCheckboxes();
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            });
        });
    </script>
@endpush
