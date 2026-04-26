@extends('backend.layout.master')


@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.supplier.index') }}">{{ __('Suppliers') }}</a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.supplier.store') }}" method="POST" class="needs-validation"
                                novalidate="">
                                @csrf
                                <div class="row">

                                    <div class="form-group col-md-6 col-6">
                                        <label>{{ __('Supplier Name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}" placeholder="Enter name" required="">
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label>{{ __('Contact Person Name') }}</label>
                                        <input type="text" class="form-control" name="contact_person"
                                            value="{{ old('contact_person') }}" placeholder="Enter contact person name"
                                            required="">
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label>{{ __('Phone') }}</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone') }}" placeholder="Enter phone" required="">
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label>{{ __('Email') }}</label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email') }}" placeholder="Enter email">
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label>{{ __('Address') }}</label>
                                        <textarea class="form-control" name="address" placeholder="Type here..."></textarea>

                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label>{{ __('Description') }}</label>
                                        <textarea class="form-control" name="description" placeholder="Type here..."></textarea>
                                    </div>
                                </div>

                                <div class="float-right">
                                    <button class="btn btn-primary" type="submit">{{ __('Create Supplier') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
