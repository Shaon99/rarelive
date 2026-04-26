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
                        <div class="card-header">
                            @if (auth()->guard('admin')->user()->can('employee_add'))
                            <a href="{{ route('admin.employee.create') }}"
                                class="btn btn-primary"><i
                                class="fas fa-plus-circle"></i> {{ __('Create Employee') }}</a>
                        @endif
                            </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Avatar</th>
                                            <th>Employee Name</th>
                                            <th>Designation</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Joining Date</th>
                                            <th>Basic Salary</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employees as $employee)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if ($employee->image)
                                                        <img src="{{ getFile('employee', $employee->image) }}"
                                                            alt="img" class="img-fluid rounded" width="40px">
                                                    @else
                                                        <img src="{{ getFile('default', $general->default_image) }}"
                                                            alt="img" class="img-fluid rounded" width="40px">
                                                    @endif
                                                </td>
                                                <td>{{ $employee->employee_name }}</td>
                                                <td>{{ $employee->designation }}</td>
                                                <td>{{ $employee->phone }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>{{ \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') }}
                                                </td>
                                                <td>{{ number_format($employee->basic_salary,2) }}</td>
                                                <td>
                                                    @if ($employee->status === 0)
                                                        <span class="badge badge-danger">{{ __('Inactive') }}</span>
                                                    @elseif($employee->status === 1)
                                                        <span class="badge badge-success">{{ __('Active') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <!-- Edit and Delete Buttons -->
                                                    @if (auth()->guard('admin')->user()->can('employee_edit'))
                                                    <a href="{{ route('admin.employee.edit', $employee->id) }}"
                                                        class="btn btn-primary btn-sm mr-1" title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    @endif
                                                    @if (auth()->guard('admin')->user()->can('employee_delete'))
                                                    <button class="btn btn-danger btn-sm delete"
                                                        data-href="{{ route('admin.employee.delete', $employee->id) }}"
                                                        data-toggle="tooltip" title="Delete" type="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
