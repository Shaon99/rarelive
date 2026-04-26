@extends('backend.layout.master')
@push('style')
    <style>
        .card-header {
            width: auto !important;
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
                    <div class="card">
                        <div class="card-body">
                            @include('backend.partial.admin-prune-logs-hint')
                            <div class="row mb-2 flex-row-reverse">
                                <div class="col-md-3">
                                    <label for="event" class="control-label">{{ __('Filter by Event') }}</label>
                                    <select name="event" id="event" class="form-control select2">
                                        <option value="" selected disabled>-- Select Event --</option>
                                        <option value="">All</option>
                                        @foreach ($events as $event)
                                            <option value="{{ $event }}"
                                                {{ request('event') == $event ? 'selected' : '' }}>
                                                {{ Str::headline($event) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive" id="activity-table">
                                @include('backend.activity.table')
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
            $('#event').on('change', function() {
                let event = $(this).val();

                $.ajax({
                    url: '{{ route('admin.activity.log') }}',
                    method: 'GET',
                    data: {
                        event: event
                    },
                    "beforeSend": function() {
                        $('#loading-overlay').show();
                    },
                    "complete": function() {
                        $('#loading-overlay').hide();
                    },
                    success: function(response) {
                        $('#activity-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            });

            // Handle pagination links
            $(document).on('click', '#activity-table .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let page = new URL(url).searchParams.get('page'); // Get the page number from the URL
                const filters = {
                    event: $('#event').val(),
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
                        $('#activity-table').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                    }
                });
            });
        });
    </script>
@endpush
