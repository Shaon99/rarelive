@extends('backend.layout.master')
@push('style')
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #676767 !important;
            line-height: 40px !important;
            text-transform: capitalize !important;
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
                    <div class="breadcrumb-item active"><a href="{{ route('admin.product.index') }}">{{ __('Products') }}</a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.expense.update',$singleExpense->id) }}" method="POST"
                                class="needs-validation" novalidate="">
                                @csrf
                                @method('PUT')
                                <div class="row">                                   
                                    <div class="form-group col-md-4 col-4">
                                        <label>{{ __('Expense Categories') }}</label>
                                        <select class="form-control select2" name="expensecategories" required="">
                                            <option value="" selected disabled>{{ __('select Expense Categories') }}</option>
                                            @forelse ($expensecategories as $item)
                                                <option value="{{ $item->id }}" {{ @$singleExpense->expense_category_id==$item->id?'selected':'' }}>{{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}
                                                </option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('expense categories can not be empty') }}
                                        </div>

                                    </div>

                                    <div class="form-group col-md-4 col-sm-12 col-lg-3 col-12">
                                        <label>{{ __('Date') }}</label>
                                        <input type="text" name="date" value="{{ @$singleExpense->date }}" class="form-control datepicker" required="">
                                        <div class="invalid-feedback">
                                            {{ __('date can not be empty') }}
                                        </div>
                                    </div>                                 

                                    <div class="form-group col-md-4 col-4">
                                        <label>{{ __('Amount') }}</label>
                                        <input type="number" class="form-control" name="amount"
                                            value="{{ old('amount',@$singleExpense->amount) }}" placeholder="enter amount"
                                            required="">
                                        <div class="invalid-feedback">
                                            {{ __('amount can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('Remark') }}</label>
                                        <textarea class="form-control" value="{{ old('note') }}" name="note"
                                            >{{ @$singleExpense->note }}</textarea>
                                       
                                    </div>                                

                                </div>
                                <div>
                                    <button class="btn btn-primary" type="submit">{{ __('Update Expense') }}</button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection

