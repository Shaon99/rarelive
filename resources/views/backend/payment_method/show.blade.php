@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.accounts.index') }}">{{ __('Accounts') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.accounts.update', $paymentMethod->id) }}" method="POST"
                                class="needs-validation" novalidate="">
                                @method('PUT')
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Account Type') }}</label>
                                        <select class="form-control  select2" name="type" required="">
                                            <option value="" selected disabled> Select Account type</option>
                                            <option value="BANK" {{ $paymentMethod->type == 'BANK' ? 'selected' : '' }}>
                                                Bank
                                            </option>
                                            <option value="MFS" {{ $paymentMethod->type == 'MFS' ? 'selected' : '' }}>
                                                MFS
                                            </option>
                                            <option value="CASH" {{ $paymentMethod->type == 'CASH' ? 'selected' : '' }}>
                                                CASH
                                            </option>
                                            <option value="STEADFAST"
                                                {{ $paymentMethod->type == 'STEADFAST' ? 'selected' : '' }}>
                                                STEADFAST</option>
                                            <option value="{{ \App\Constants\CommonConstant::CARRYBEE }}"
                                                {{ $paymentMethod->type == \App\Constants\CommonConstant::CARRYBEE ? 'selected' : '' }}>
                                                CARRYBEE</option>
                                        </select>
                                        <div class="invalid-feedback"> {{ __('type can not be empty') }} </div>
                                    </div>
                                    <div class="form-group col-md-4"> <label>{{ __('Name') }}</label> <input
                                            type="text" class="form-control" name="name"
                                            value="{{ old('name', $paymentMethod->name) }}" placeholder="Enter name"
                                            required="">
                                        <div class="invalid-feedback"> {{ __('name can not be empty') }} </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Account Number') }}</label>
                                        <input type="number" class="form-control" name="account_number"
                                            value="{{ old('account_number', $paymentMethod->account_number) }}"
                                            placeholder="Enter account number">
                                    </div>
                                    <div class="form-group col-md-4"> <label>{{ __('Branch Name') }}</label> <input
                                            type="text" class="form-control" name="branch_name"
                                            value="{{ old('branch_name', $paymentMethod->branch_name) }}"
                                            placeholder="Enter branch name">
                                    </div>
                                    <div class="form-group col-md-4"> <label>{{ __('Account Name') }}</label>
                                        <input type="text" class="form-control" name="account_name"
                                            value="{{ old('account_name', $paymentMethod->account_name) }}"
                                            placeholder="Enter account name">
                                    </div>
                                    <div class="form-group col-md-4">
                                        @php
                                            $isSteadfast =
                                                $paymentMethod->type === \App\Constants\CommonConstant::STEADFAST;
                                            $currentBalance = $isSteadfast
                                                ? ($steadfast_current_balance === 'Unavailable'
                                                    ? 0
                                                    : $steadfast_current_balance)
                                                : $paymentMethod->current_balance;
                                        @endphp

                                        <label>{{ __('Current Balance') }}</label>
                                        <input type="number" class="form-control" name="current_balance" readonly
                                            value="{{ old('current_balance', $currentBalance) }}"
                                            placeholder="Enter current balance">
                                    </div>

                                    <div class="form-group col-md-4"> <label>{{ __('Phone') }}</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone', $paymentMethod->phone) }}"
                                            placeholder="Enter phone number">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Address') }}</label>
                                        <textarea name="address" id="" class="form-control" rows="2" placeholder="Type here...">{{ $paymentMethod->address }}</textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary" type="submit">{{ __('Update Account') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
