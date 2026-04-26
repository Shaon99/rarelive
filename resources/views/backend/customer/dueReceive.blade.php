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

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.dueReceivePost') }}" method="POST" class="needs-validation" novalidate=""
                    id="dueForm">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Select Customer') }}</label>
                            <select class="form-control select2 selectCustomer" name="customer" required="">
                                <option value="" selected disabled>{{ __('select customer') }}</option>
                                @forelse ($dueCustomers as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @empty
                                <option disabled>{{ __('no record found') }}
                                </option>
                                @endforelse
                            </select>
                            <div class="invalid-feedback">
                                {{ __('please select a customer') }}
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Due Amount') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control due_amount" name="due_amount" id="due_amount"
                                    value="{{ old('due_amount') }}" readonly placeholder="due amount">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        BDT
                                    </div>
                                </div>
                            </div>
                            <div class="row dueList">
                                
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Receive Amount') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control receive_amount" name="receive_amount"
                                    id="due_amount" value="{{ old('receive_amount') }}"
                                    placeholder="Enter receive amount" required="">

                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        BDT
                                    </div>
                                </div>
                                <div class="invalid-feedback">
                                    {{ __('receive amount can not be empty') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Present Due Amount') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control current_due" name="current_due"
                                    value="{{ old('current_due') }}" readonly placeholder="current due amount">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        BDT
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end py-3">
                        <button class="btn btn-primary py-2" type="submit">{{ __('Submit Amount') }}</button>
                    </div>
                </form>
                <hr>
                <div class="card-header d-flex justify-content-end">
                    @if (request()->has('trashed'))
                    <a href="{{ route('admin.dueReceive') }}" class="btn btn-primary" data-toggle="tooltip"
                        title="due receive list">
                        Due Receive
                    </a>
                    @else
                    <a href="{{ route('admin.dueReceive', ['trashed' => 'DeletedRecords']) }}" class="btn btn-danger"
                        data-toggle="tooltip" title="recycle bin">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </a>
                    @endif
                </div>
                <div class="table-responsive mt-3">
                    <table id="table_1" class="table">
                        <thead>
                            <tr>
                                <th>{{ __('SL') }}</th>
                                <th>{{ __('#ID') }}</th>
                                <th>{{ __('Customer Name') }}</th>
                                <th>{{ __('Previous Due') }}</th>
                                <th>{{ __('Receive Amount') }}</th>
                                <th>{{ __('Current Due') }}</th>
                                <th>{{ __('Receive Date') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rerceiveList as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ @$item->id }}</td>
                                <td>{{ @$item->customer->name }}</td>
                                <td>{{ number_format(@$item->previous_due,2). ' ' . @$general->site_currency }}</td>
                                <td>{{ number_format(@$item->receive_amount,2). ' ' . @$general->site_currency }}</td>
                                <td>{{ number_format(@$item->current_due,2). ' ' . @$general->site_currency }}</td>
                                <td>{{ @$item->created_at->format('d M, Y') }}</td>
                                <td>
                                    @if (request()->has('trashed'))
                                    <a class="btn btn-success btn-action mr-1"
                                        href="{{ route('admin.dueReceiveRestore', $item->id) }}" data-toggle="tooltip"
                                        title="restore">
                                        <i class="fas fa-trash-restore"></i>
                                    </a>
                                    <button class="btn btn-danger deleteforever"
                                        data-href="{{ route('admin.dueReceiveDeleteForever', $item->id) }}"
                                        data-toggle="tooltip" title="Deleteforever" type="button">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <a href="{{ route('admin.dueReceiveReceipt',$item->id) }}"
                                        class="btn btn-success btn-action mr-1" data-toggle="tooltip" title="receipt">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <a href="javascript:void(0)" data-item="{{ $item }}"
                                        data-href="{{ route('admin.dueReceiveUpdate',$item->id) }}"
                                        class="btn btn-primary btn-action editDueReceive mr-1" data-toggle="tooltip"
                                        title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-danger delete mr-1"
                                        data-href="{{ route('admin.dueReceiveDelete', $item->id) }}"
                                        data-toggle="tooltip" title="Delete" type="button">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
</div>

<div class="modal fade" tabindex="-1" id="editDueReceive" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Due Receive') }} <span class="name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Select Customer') }}</label>
                            <select class="form-control select2 selectCustomerE" name="customerE" required="">
                                <option value="" selected disabled>{{ __('select customer') }}</option>
                                @forelse ($dueCustomers as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @empty
                                <option disabled>{{ __('no record found') }}
                                </option>
                                @endforelse
                            </select>
                            <div class="invalid-feedback">
                                {{ __('please select a customer') }}
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Due Amount') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control due_amountE" name="due_amountE"
                                    value="{{ old('due_amount') }}" readonly placeholder="due amount">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        BDT
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Receive Amount') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control receive_amountE" name="receive_amountE"
                                    id="due_amount" value="{{ old('receive_amount') }}"
                                    placeholder="Enter receive amount" required="">

                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        BDT
                                    </div>
                                </div>
                                <div class="invalid-feedback">
                                    {{ __('receive amount can not be empty') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-12 col-12">
                            <label>{{ __('Present Due Amount') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control current_dueE" name="current_dueE"
                                    value="{{ old('current_due') }}" readonly placeholder="current due amount">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        BDT
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>

                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('script')
<script>
    'use strict';

    $(document).on("change", ".selectCustomer", function () {
        const id = $(this).val();
        let url = "{{ route('admin.getDue', ':id') }}";
        url = url.replace(":id", id);
        $("#due_amount").addClass('btn-progress');
        $.ajax({
        type: "GET",
        url: url,
        success: (data) => {
        $('#due_amount').val(data.previous_due.previous_due);

        // const dueList = $(".dueList");
        // const item = data.customerDue;
        //         // Clear the previous data
        //         dueList.empty();

        //             item.forEach(function (value) {
        //                 var newLi = `
                        
        //                 <div class="input-group mt-2 col-md-6">
        //                     <input type="number" class="form-control" name="due_amount[]"
        //                         value="${value.due_amount}" placeholder="due amount">
        //                         <input class="form-control" name="id[]"
        //                         value="${value.id}" hidden> 
        //                     <div class="input-group-append">
        //                         <div class="input-group-text">
        //                             BDT
        //                         </div>
        //                     </div>
        //                 </div>
        //                 `;
        //                 dueList.append(newLi);
        // });

        },
        complete: function () {
            $("#due_amount").removeClass('btn-progress');
        },
        error: (error) => {},
        });
    });

    $(document).on("change", ".selectCustomerE", function () {
        const id = $(this).val();
        let url = "{{ route('admin.getDue', ':id') }}";
        url = url.replace(":id", id);
        $(".due_amountE").addClass('btn-progress');
        $.ajax({
        type: "GET",
        url: url,
        success: (data) => {
        var p=parseFloat($('.receive_amountE').val());
        $('.due_amountE').val(data.previous_due.previous_due + p);
        },
        complete: function () {
            $(".due_amountE").removeClass('btn-progress');
        },
        error: (error) => {},
        });
    });

    $(document).on("keyup", ".receive_amount", function () {
        var due = $('.due_amount').val();
        var currentDue=due - $(this).val();
        $('.current_due').val(currentDue);
    });

    $(document).on("keyup", ".receive_amountE", function () {
        var dueE = $('.due_amountE').val();
        var currentDueE=dueE - $(this).val();
        $('.current_dueE').val(currentDueE);
    });

    $('.editDueReceive').on('click', function() {        
        const modal = $('#editDueReceive');
        const item = $(this).data('item');
        $('.selectCustomerE').val(item.customer_id).trigger('change');
        $('.due_amountE').val(item.previous_due);
        $('.receive_amountE').val(item.receive_amount);
        $('.current_dueE').val(item.current_due);
        modal.find('form').attr('action', $(this).data('href'));
        modal.modal('show');
    });
</script>
@endpush