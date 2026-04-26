@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="card">
                        <form method="post" action="{{ route('admin.change.password') }}" class="needs-validation"
                            novalidate="">
                            @csrf
                            <div class="card-body">
                                <h6 class="mb-4">{{ __('Change Password') }}</h6>
                                <div class="row">
                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('Old Password') }}</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" placeholder="Old Password"
                                                name="old_password" required="">
                                            <div class="input-group-append">
                                                <span class="input-group-text toggle-password cursor-pointer"
                                                    data-target="old_password">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('New Password') }}</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="password"
                                                placeholder="New Password" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text toggle-password cursor-pointer"
                                                    data-target="password">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('Confirm Password') }}</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="password_confirmation"
                                                placeholder="Confirm Password" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text toggle-password cursor-pointer"
                                                    data-target="password_confirmation">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary btn-icon btn-sm"><i class="fas fa-save"></i> {{ __('Change Password') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8 col-lg-8">
                    <div class="card">
                        <form method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data"
                            class="needs-validation" novalidate="">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label> Avatar </label>
                                        <div id="image-preview" class="image-preview"
                                            style="background-image:url({{ getFile('admin', auth()->guard('admin')->user()->image) }});">
                                            <label for="image-upload" id="image-label">{{ __('Choose File') }}</label>
                                            <input type="file" name="image" id="image-upload" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="form-group col-12">
                                                <label>{{ __('Name') }}</label>
                                                <input type="text" class="form-control" name="name"
                                                    value="{{ auth()->guard('admin')->user()->name }}" required="">
                                                <div class="invalid-feedback">
                                                    {{ __('name can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-12">
                                                <label>{{ __('Phone') }}</label>
                                                <input type="text" class="form-control" name="phone"
                                                    value="{{ auth()->guard('admin')->user()->phone }}" required="">
                                                <div class="invalid-feedback">
                                                    {{ __('phone can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-12">
                                                <label>{{ __('Email') }}</label>
                                                <input type="email" class="form-control" name="email"
                                                    value="{{ auth()->guard('admin')->user()->email }}" required="">
                                                <div class="invalid-feedback">
                                                    {{ __('email can not be empty') }}
                                                </div>
                                            </div>
                                            <div class="form-group col-12">
                                                <label>{{ __('Username') }}</label>
                                                <input type="text" class="form-control" name="username" required
                                                    value="{{ auth()->guard('admin')->user()->username }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary btn-icon btn-sm"><i class="fas fa-save"></i> {{ __('Update Profile') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        $(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label",
                label_default: "{{ __('Choose File') }}",
                label_selected: "{{ __('Update Image') }}",
                no_label: false,
                success_callback: null
            });
        })
    </script>
@endpush
