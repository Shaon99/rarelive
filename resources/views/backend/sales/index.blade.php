@extends('backend.layout.master')
@push('style')
    <style>
        .toggle-btn-new {
            width: 16px;
            height: 16px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            box-shadow: none;
            outline: none;
        }
    </style>
@endpush
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb d-none d-md-flex">
                    <div class="breadcrumb-item"> {{ __($pageTitle) }} </div>
                    <div class="breadcrumb-item active">
                        <a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-end pt-1 pb-1">
                            @if (auth()->guard('admin')->user()->can('order_add'))
                                <a href="{{ route('admin.sales.create') }}" title="Create Order"
                                    class="btn btn-icon icon-left btn-primary">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Create order
                                </a>
                            @endif
                        </div>
                        <div class="card-body pt-1">
                            <div id="alert-container">
                            </div>
                            <!-- Start coding here -->
                            <livewire:sales-table />
                        </div>
                    </div>
                </div>
        </section>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('.saleCheckbox').on('change', function() {
                let selected = $('.saleCheckbox:checked').length > 0;
                $('#deleteSelectedSale').prop('disabled', !selected);
                if (selected) {
                    $('#deleteSelectedSale').removeClass('d-none');
                } else {
                    $('#deleteSelectedSale').addClass('d-none');
                }
            });

            // Select All functionality
            $('#selectAllSale').on('change', function() {
                $('.saleCheckbox').prop('checked', $(this).prop('checked')).trigger('change');
            });

            // Handle delete confirmation
            $('#selectedDeleteButton').on('click', function() {
                let selectedIds = $('.saleCheckbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    var alertHtml = `
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            Please select at least one item before proceeding.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    $('#alert-container').html(alertHtml);
                    return;
                }

                // Populate the hidden input field with selected IDs
                $('#salesIds').val(selectedIds.join(','));

                // Submit the form using POST
                $('#deleteForm').submit();
            });

            $('#sendToSteadFAst').on('click', function() {
                var selectedIds = [];
                $('input[name="sales[]"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    var alertHtml = `
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            Please select at least one item before proceeding.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    $('#alert-container').html(alertHtml);
                    return;
                } else {
                    $('#alert-container').html('');
                }

                var selectedIDsString = selectedIds.join(',');

                // Send AJAX request
                $.ajax({
                    url: '{{ route('admin.sales.sendToSteadFast') }}',
                    type: 'POST',
                    data: {
                        ids: selectedIDsString
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#sendToSteadFAst').prop('disabled', true);
                        $('#sendToSteadFAst').addClass('btn-progress');
                    },
                    complete: function() {
                        $('#sendToSteadFAst').prop('disabled', false);
                        $('#sendToSteadFAst').removeClass('btn-progress');
                    },
                    success: function(response) {
                        var successHtml = `
                            <div class="alert alert-success alert-dismissible" role="alert">
                                Orders sent and updated successfully.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        `;
                        $('#alert-container').html(successHtml);

                        // Open print view in new tab
                        var printUrl = '/invoices/print?ids=' + selectedIds.join(',');
                        window.open(printUrl, '_blank');

                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        var errorHtml =
                            `<div class="alert alert-danger alert-dismissible" role="alert"><strong>${xhr.responseJSON.message}</strong><br>`;

                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            if (errors) {
                                errors.forEach(function(err) {
                                    errorHtml += `<p>${err}</p>`;
                                });
                            } else {
                                errorHtml += `<p>Unknown error occurred.</p>`;
                            }
                        }
                        if (xhr.status === 404) {
                            var message = xhr.responseJSON.message;
                            errorHtml += `<p>${message}</p>`;
                        }
                        errorHtml += `
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`;

                        $('#alert-container').html(errorHtml);
                    }
                });
            });

            $('#sendToCarrybee').on('click', function() {
                var selectedIds = [];
                $('input[name="sales[]"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    var alertHtml = `
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            Please select at least one item before proceeding.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    $('#alert-container').html(alertHtml);
                    return;
                }

                $('#alert-container').html('');

                var accountKey = $('#carrybeeAccountSelect').val();
                if (!accountKey) {
                    $('#alert-container').html(`
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            Select a Carrybee account.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `);
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.sales.sendToCarrybee') }}',
                    type: 'POST',
                    data: {
                        ids: selectedIds.join(','),
                        carrybee_account_key: accountKey
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#sendToCarrybee').prop('disabled', true).addClass('btn-progress');
                    },
                    complete: function() {
                        $('#sendToCarrybee').prop('disabled', false).removeClass(
                            'btn-progress');
                    },
                    success: function(response) {
                        var msg = (response && response.message) ? response.message :
                            'Orders updated successfully.';
                        var extra = '';
                        if (response && response.errors && response.errors.length) {
                            extra = '<ul class="mb-0 mt-2 pl-3">';
                            response.errors.forEach(function(err) {
                                extra += '<li>' + err + '</li>';
                            });
                            extra += '</ul>';
                        }
                        var cls = (response && response.errors && response.errors.length) ?
                            'alert-warning' : 'alert-success';
                        $('#alert-container').html(`
                            <div class="alert ${cls} alert-dismissible" role="alert">
                                ${msg}${extra}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        `);
                        location.reload();
                    },
                    error: function(xhr) {
                        var errorHtml =
                            `<div class="alert alert-danger alert-dismissible" role="alert"><strong>${(xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Request failed'}</strong><br>`;

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            xhr.responseJSON.errors.forEach(function(err) {
                                errorHtml += `<p>${err}</p>`;
                            });
                        }
                        errorHtml += `
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`;

                        $('#alert-container').html(errorHtml);
                    }
                });
            });

            //multiple invoice generation print
            $('#print-invoices-btn').on('click', function() {
                var selectedIds = [];
                $('input[name="sales[]"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    var alertHtml = `
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            Please select at least one item before printing.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    $('#alert-container').html(alertHtml);
                    return;
                }

                // Open print route in new tab
                var printUrl = '/invoices/print?ids=' + selectedIds.join(',');
                window.open(printUrl, '_blank');
            });
            //multiple level label print
            $('#print-level-labels-btn').on('click', function() {
                var selectedIds = [];
                $('input[name="sales[]"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    var alertHtml = `
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            Please select at least one item before printing.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    $('#alert-container').html(alertHtml);
                    return;
                }

                // Open level print route in new tab
                var printUrl = '/sales-level-print-bulk?ids=' + selectedIds.join(',');
                window.open(printUrl, '_blank');
            });
        });
    </script>
@endpush
