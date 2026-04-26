@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.index') }}">{{ __('Admin User List') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.update', $admin->id) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4 mb-4">
                                        <label class=""> {{ __('Avatar') }} (360 x 360)</label>
                                        <div id="image-preview" class="image-preview"
                                            style="background-image:url({{ getFile('admin', $admin->image) }});">
                                            <label for="image-upload" id="image-label">{{ __('Choose File') }}</label>
                                            <input type="file" name="image" id="image-upload" />
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Employees') }}</label>
                                                <select class="form-control select2" name="employee_id" id="employee">
                                                    <option value="" selected disabled>-- {{ __('Select Employee') }}
                                                        --
                                                    </option>
                                                    @foreach ($employees as $employee)
                                                        <option value="{{ $employee->id }}"
                                                            {{ $employee->id == $admin->employee_id ? 'selected' : '' }}
                                                            data-name="{{ $employee->employee_name }}"
                                                            data-email="{{ $employee->email }}"
                                                            data-phone="{{ $employee->phone }}">
                                                            {{ __($employee->employee_name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Name') }}</label>
                                                <input type="text" name="name" id="employee_name" class="form-control"
                                                    value="{{ $admin->name }}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Phone') }}</label>
                                                <input type="text" name="phone" id="employee_phone"
                                                    class="form-control" value="{{ $admin->phone }}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Email') }}</label>
                                                <input type="text" name="email" id="employee_email"
                                                    class="form-control" value="{{ $admin->email }}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('User Name') }}</label>
                                                <input type="text" name="username" class="form-control"
                                                    value="{{ $admin->username }}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Password') }}</label>
                                                <div class="input-group">
                                                    <input type="password" name="password" class="form-control"
                                                        placeholder="Enter password">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text toggle-password cursor-pointer"
                                                            data-target="password">
                                                            <i class="fas fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Password Confirmation') }}</label>
                                                <div class="input-group">
                                                    <input type="password" name="password_confirmation" class="form-control"
                                                        placeholder="Confirm Password">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text toggle-password cursor-pointer"
                                                            data-target="password_confirmation">
                                                            <i class="fas fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Role') }}</label>
                                                <select class="form-control select2" name="role">
                                                    <option value="" selected disabled>-- {{ __('Select Role') }} --
                                                    </option>
                                                    @foreach (@$roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            @if ($admin->getRoleNames()[0] == $role->name) selected @endif>
                                                            {{ __($role->name) }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    {{ __('Role can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Select Branch') }}</label>
                                                <select class="form-control select2" name="warehouse_id" required="">
                                                    <option value="" selected disabled>-- {{ __('Select Branch') }}
                                                        --
                                                    </option>
                                                    @foreach (@$warehouse as $item)
                                                        <option value="{{ $item->id }}"
                                                            {{ $admin->warehouse_id == $item->id ? 'selected' : '' }}>
                                                            {{ __($item->name) }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    {{ __('warehouse can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-md-12">
                                                <button type="submit" 
                                                    class="btn btn-primary float-right">{{ __('Save Changes') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        $(function() {
            'use strict'

            $.uploadPreview({
                input_field: "#image-upload", // Default: .image-upload
                preview_box: "#image-preview", // Default: .image-preview
                label_field: "#image-label", // Default: .image-label
                label_default: "{{ __('Choose File') }}", // Default: Choose File
                label_selected: "{{ __('Update Image') }}", // Default: Change File
                no_label: false, // Default: false
                success_callback: null // Default: null
            });
        })

        $('#employee').on('change', function() {
            const selected = $(this).find(':selected');

            const name = selected.data('name');
            const email = selected.data('email');
            const phone = selected.data('phone');

            $('#employee_name').val(name);
            $('#employee_email').val(email);
            $('#employee_phone').val(phone);
        });
    </script>
@endpush
