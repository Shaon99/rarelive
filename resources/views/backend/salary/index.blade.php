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
                        <div class="card-header justify-content-end">
                            @if (auth()->guard('admin')->user()->can('salary_add'))
                                <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary"><i
                                        class="fas fa-plus-circle"></i> {{ __('Generate Salary') }}</a>
                            @endif
                        </div>
                        <div class="card-body mt-0 pt-0">
                            <div class="row mb-2 mt-0">
                                <div class="col-md-3">
                                    <label for="event" class="control-label">{{ __('Filter by Employee') }}</label>
                                    <select name="employee" id="employee" class="form-control select2">
                                        <option value="" selected disabled>Select Employee</option>
                                        @foreach ($employee as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->employee_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="event" class="control-label">{{ __('Filter by Employee') }}</label>
                                    <select class="form-control form-select select2" name="salary_for" id="salary_for">
                                        <option value="" selected disabled>Select Month
                                        </option>
                                        <option value="January">January -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="February">February -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="March">March -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="April">April -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="May">May -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="June">June -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="July">July -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="August">August -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="September">September -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="October">October -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="November">November -
                                            <?php echo date('Y'); ?>
                                        </option>
                                        <option value="December">December -
                                            <?php echo date('Y'); ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="control-label">{{ __('Filter by Status') }}</label>
                                    <select class="form-control form-select select2" name="status" id="status">
                                        <option value="" selected disabled>Select Status
                                        </option>
                                        <option value="Due">Due
                                        </option>
                                        <option value="Paid">Paid
                                        </option>
                                        <option value="Partial">Partial
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="button" id="filters" class="btn btn-primary btn-block">
                                        <span id="filter">{{ __('Filter') }}</span>
                                        <span id="loading-spinner" class="spinner-border spinner-border-sm ml-2 d-none"
                                            role="status" aria-hidden="true"></span>
                                    </button>
                                </div>

                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="button" id="resetFilters" class="btn btn-danger btn-block">
                                        {{ __('Reset') }}
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive" id="salary-table">
                                @include('backend.salary.table')
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
            $(document).on('click', '#filters', function(event) {
                let employee = $('#employee').val();
                let salary_for = $('#salary_for').val();
                let status = $('#status').val();
                var $btn = $(this);

                $.ajax({
                    url: '{{ route('admin.salary.index') }}',
                    method: 'GET',
                    data: {
                        employee: employee,
                        salary_for: salary_for,
                        status: status
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
                        $('#salary-table').html(response);
                    }
                });
            });

            $(document).on('click', '#filters', function(event) {
                let employee = $('#employee').val();
                let salary_for = $('#salary_for').val();
                let status = $('#status').val();
                var $btn = $(this);

                $.ajax({
                    url: '{{ route('admin.salary.index') }}',
                    method: 'GET',
                    data: {
                        employee: employee,
                        salary_for: salary_for,
                        status: status
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
                        $('#salary-table').html(response);
                    }
                });
            });

            // Reset button functionality
            $(document).on('click', '#resetFilters', function() {
                // Reset filter fields
                $('#employee').val('').trigger('change');
                $('#salary_for').val('').trigger('change');
                $('#status').val('').trigger('change');

                // Reload the table with default data (no filters)
                $.ajax({
                    url: '{{ route('admin.salary.index') }}',
                    method: 'GET',
                    beforeSend: function() {
                        $('#filter').text('Resetting...');
                        $('#filters').prop('disabled', true);
                        $('#loading-spinner').removeClass('d-none');
                        $('#loading-overlay').show();
                    },
                    complete: function() {
                        $('#filters').prop('disabled', false);
                        $('#filter').text('Filter');
                        $('#loading-spinner').addClass('d-none');
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#salary-table').html(response);
                    }
                });
            });


            // Handle pagination links
            $(document).on('click', '#salary-table .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let page = new URL(url).searchParams.get('page');
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
                        $('#salary-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            });
        });
    </script>
@endpush
