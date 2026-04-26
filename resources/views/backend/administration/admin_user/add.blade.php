@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.index') }}">{{ __('Admin List') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.store') }}" method="post" class="needs-validation" novalidate=""
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group col-md-12 mb-4">
                                            <label class=""> {{ __('Avatar') }} (360 x 360)</label>
                                            <div id="image-preview" class="image-preview">
                                                <label for="image-upload" id="image-label">{{ __('Choose File') }}</label>
                                                <input type="file" name="image" id="image-upload" />
                                            </div>
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
                                                            data-name="{{ $employee->employee_name }}"
                                                            data-email="{{ $employee->email }}"
                                                            data-phone="{{ $employee->phone }}">
                                                            {{ __($employee->employee_name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Name') }}</label>
                                                <input type="text" name="name" id="employee_name" class="form-control"
                                                    placeholder="Enter name" required>
                                            </div>
                                       
                                            <div class="form-group col-md-6">
                                                <label>{{ __('Phone') }}</label>
                                                <input type="text" name="phone" id="employee_phone"
                                                    class="form-control" placeholder="Enter phone" required>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Email') }}</label>
                                                <input type="email" name="email" id="employee_email"
                                                    class="form-control" placeholder="Enter email" required>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Username') }}</label>
                                                <input type="text" name="username" class="form-control"
                                                    value="{{ old('username') }}" placeholder="Username" required="">
                                            </div>


                                            <div class="form-group col-md-6">
                                                <label for="">{{ __('Password') }}</label>
                                                <div class="input-group">
                                                    <input type="password" name="password" class="form-control"
                                                        placeholder="Enter password" required="">
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
                                                        placeholder="Confirm Password" required="">
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
                                                <select class="form-control select2" name="role" required="">
                                                    <option value="" selected disabled>-- {{ __('Select Role') }} --
                                                    </option>
                                                    @foreach (@$roles as $role)
                                                        <option value="{{ $role->name }}">{{ __($role->name) }}
                                                        </option>
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
                                                        <option value="{{ $item->id }}">{{ __($item->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    {{ __('warehouse can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-md-12">
                                                <button type="submit"
                                                    class="btn btn-primary float-right">{{ __('Create Admin User') }}</button>
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
