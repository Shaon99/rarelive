@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.employee.index') }}">{{ __('Employees') }}</a></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.employee.update', $employee->id) }}" method="POST"
                                class="needs-validation" novalidate="" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label class="col-form-label">{{ __('Avatar') }} (360 x 360)</label>
                                        <div id="image-preview" class="image-preview"
                                            style="background-image:url({{ getFile('employee', $employee->image) }}); background-repeat:no-repeat; background-size:100% 100%;">
                                            <label for="image-upload" id="image-label">{{ __('Choose File') }}</label>
                                            <input type="file" name="image" id="image-upload" />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>{{ __('Employee Name') }}</label>
                                                <input type="text" class="form-control" name="employee_name"
                                                    value="{{ old('employee_name', $employee->employee_name) }}"
                                                    placeholder="Enter employee name" required="">
                                                <div class="invalid-feedback">
                                                    {{ __('Employee name can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Designation') }}</label>
                                                <input type="text" class="form-control" name="designation"
                                                    value="{{ old('designation', $employee->designation) }}"
                                                    placeholder="Enter employee designation" required="">
                                                <div class="invalid-feedback">
                                                    {{ __('Designation can not be empty') }}
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Joining Date') }}</label>
                                                <input type="text" class="form-control datepicker" name="joining_date"
                                                    value="{{ $employee->joining_date }}"
                                                    placeholder="Select Joining date">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Phone') }}</label>
                                                <input type="text" class="form-control" name="phone"
                                                    value="{{ old('phone', $employee->phone) }}"
                                                    placeholder="Enter phone number" required="">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Email') }}</label>
                                                <input type="email" class="form-control" name="email"
                                                    value="{{ old('email', $employee->email) }}"
                                                    placeholder="Enter email address">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Basic Salary') }}</label>
                                                <input type="number" class="form-control" name="basic_salary"
                                                    value="{{ old('basic_salary', $employee->basic_salary) }}"
                                                    placeholder="Enter basic salary" required="">
                                                    <div class="invalid-feedback">
                                                        {{ __('Basic can not be empty') }}
                                                    </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Documents') }}</label>
                                                <input type="file" class="form-control" name="documents[]" multiple>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{ __('Previous Documents') }}</label>
                                                @foreach ($documents as $document)
                                                    <li>
                                                        <a href="{{ getFile('employee', @$document) }}" target="_blank">
                                                            {{ basename($document) }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary" type="submit">{{ __('Update Employee') }}</button>
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
        $.uploadPreview({
            input_field: "#image-upload",
            preview_box: "#image-preview",
            label_field: "#image-label",
            label_default: "Choose File",
            label_selected: "Update Image",
            no_label: false,
            success_callback: null
        });
    </script>
@endpush
