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
                        <div class="card-header d-flex justify-content-end">
                        @if (auth()->guard('admin')->user()->can('customer_add'))
                            <a href="{{ route('admin.customer.create') }}"
                                class="btn btn-icon icon-left btn-primary"> <i class="fas fa-plus-circle"></i> {{ __('Create Customer') }}</a>
                        @endif
                            </div>
                        <div class="card-body">
                            <livewire:customer-table />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
