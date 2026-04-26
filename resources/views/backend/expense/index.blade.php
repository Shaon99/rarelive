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
                        <div class="card-header d-flex justify-content-between">
                            @if (auth()->guard('admin')->user()->can('expense_add'))
                                <a href="{{ route('admin.expense.create') }}" class="btn btn-icon icon-left btn-primary"><i
                                        class="fas fa-plus-circle"></i> {{ __('Create Expense') }}</a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row mb-2 flex-row-reverse">
                                <div class="col-md-3">
                                    <label for="event" class="control-label">{{ __('Filter by Expense Category') }}</label>
                                    <select name="category" id="category" class="form-control select2">
                                        <option value="" selected disabled>-- Select Category --</option>
                                        <option value="">All</option>
                                        @foreach ($categories as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive" id="expense-table">
                                @include('backend.expense.expense-table')
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
        $(document).ready(function() {
            $('#category').on('change', function() {
                let category = $(this).val();

                $.ajax({
                    url: '{{ route('admin.expense.index') }}',
                    method: 'GET',
                    data: {
                        category: category
                    },
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#expense-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            });

            // Handle pagination links
            $(document).on('click', '#expense-table .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let page = new URL(url).searchParams.get('page'); // Get the page number from the URL
                const filters = {
                    page: page
                };
                $.ajax({
                    url: url,
                    data: filters,
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#expense-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            });
        });
    </script>
@endpush