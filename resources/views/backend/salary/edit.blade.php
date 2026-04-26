@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.salary.index') }}">{{ __('Salaries') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if (Session::has('errorSalary'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Warning!</strong> {{ session('errorSalary') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <form action="{{ route('admin.salaries.update', $salary->id) }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Select Employee') }}</label>
                                        <select name="employee_id" id="employeeSelect"
                                            class="form-control form-select select2" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}"
                                                    data-basic="{{ $employee->basic_salary }}"
                                                    {{ $salary->employee_id == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->employee_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('Please select an employee.') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Basic Salary') }}</label>
                                        <input type="number" class="form-control" value="{{ $salary->basic_salary }}"
                                            id="basicSalary" name="basic_salary" placeholder="Enter basic salary" readonly
                                            required>
                                        <div class="invalid-feedback">
                                            {{ __('Basic salary is required.') }}
                                        </div>
                                    </div>

                                    <!--<div class="form-group col-md-4">-->
                                    <!--    <label>{{ __('Other Allowances') }}</label>-->
                                    <!--    <input type="number" class="form-control" value="{{ $salary->others }}"-->
                                    <!--        name="others" placeholder="Enter other allowances">-->
                                    <!--</div>-->

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Over Time Amount') }}</label>
                                        <input type="number" class="form-control" name="over_time"
                                            value="{{ $salary->over_time }}" placeholder="Enter overtime amount">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Bonus') }}</label>
                                        <input type="number" class="form-control" name="bonus"
                                            value="{{ $salary->bonus }}" placeholder="Enter bonus amount">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Deductions') }}</label>
                                        <input type="number" class="form-control" name="deductions"
                                            value="{{ $salary->deductions }}" placeholder="Enter deductions">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Advance') }}</label>
                                        <input type="number" class="form-control" name="advance"
                                            value="{{ $salary->advance }}" placeholder="Enter advance amount">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Total Salary') }}</label>
                                        <input type="number" id="totalSalary" class="form-control" name="total_salary"
                                            value="{{ $salary->total_salary }}" placeholder="Total salary" readonly>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label>{{ __('Salary For') }}</label>
                                        <select class="form-control form-select select2" name="salary_for" required="">
                                            <option value="" selected disabled>Select Month
                                            </option>
                                            <option value="January"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'January-' . date('Y') ? 'selected' : '' }}>
                                                January-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="February"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'February-' . date('Y') ? 'selected' : '' }}>
                                                February-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="March"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'March-' . date('Y') ? 'selected' : '' }}>
                                                March-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="April"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'April-' . date('Y') ? 'selected' : '' }}>
                                                April-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="May"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'May-' . date('Y') ? 'selected' : '' }}>
                                                May-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="June"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'June-' . date('Y') ? 'selected' : '' }}>
                                                June-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="July"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'July-' . date('Y') ? 'selected' : '' }}>
                                                July-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="August"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'August-' . date('Y') ? 'selected' : '' }}>
                                                August-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="September"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'September-' . date('Y') ? 'selected' : '' }}>
                                                September-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="October"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'October-' . date('Y') ? 'selected' : '' }}>
                                                October-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="November"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'November-' . date('Y') ? 'selected' : '' }}>
                                                November-
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="December"
                                                {{ \Carbon\Carbon::parse($salary->salary_for)->format('F-Y') === 'December-' . date('Y') ? 'selected' : '' }}>
                                                December-
                                                <?php echo date('Y'); ?>
                                            </option>
                                        </select>
                                    </div>                                   

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Note') }}</label>
                                        <textarea class="form-control" name="note" rows="3" placeholder="Enter additional notes">{{ $salary->note }}</textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Generate Salary</button>
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
        $(document).ready(function() {
            $('#employeeSelect').on('change', function() {
                const basicSalary = $(this).find(':selected').data('basic') || '';
                $('#basicSalary').val(basicSalary);
            });

            function calculateTotalSalary() {
                const basicSalary = parseFloat($('#basicSalary').val()) || 0;
                const overTime = parseFloat($('input[name="over_time"]').val()) || 0;
                const bonus = parseFloat($('input[name="bonus"]').val()) || 0;
                const deductions = parseFloat($('input[name="deductions"]').val()) || 0;
                const advance = parseFloat($('input[name="advance"]').val()) || 0;

                const totalSalary = basicSalary + overTime + bonus - (deductions+advance);

                $('#totalSalary').val(totalSalary);
            }

            // Populate Basic Salary when an employee is selected
            $('#employeeSelect').on('change', function() {
                const basicSalary = $(this).find(':selected').data('basic') || 0;
                $('#basicSalary').val(basicSalary);
                calculateTotalSalary();
            });

            $('input[name="over_time"], input[name="bonus"], input[name="deductions"], input[name="advance"]').on(
                'input',
                function() {
                    calculateTotalSalary();
                });

        });
    </script>
@endpush
