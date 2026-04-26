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
                            <form action="{{ route('admin.salaries.store') }}" method="POST" class="needs-validation"
                                novalidate>
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Select Employee') }}</label>
                                        <select name="employee_id" id="employeeSelect"
                                            class="form-control form-select select2" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}"
                                                    data-basic="{{ $employee->basic_salary }}">
                                                    {{ $employee->employee_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('Please select an employee.') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Basic Salary') }}</label>
                                        <input type="number" class="form-control" id="basicSalary" name="basic_salary"
                                            placeholder="Enter basic salary" readonly required>
                                        <div class="invalid-feedback">
                                            {{ __('Basic salary is required.') }}
                                        </div>
                                    </div>

                                    <!--<div class="form-group col-md-4">-->
                                    <!--    <label>{{ __('Other Allowances') }}</label>-->
                                    <!--    <input type="number" class="form-control" name="others"-->
                                    <!--        placeholder="Enter other allowances">-->
                                    <!--</div>-->

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Over Time Amount') }}</label>
                                        <input type="number" class="form-control" name="over_time"
                                            placeholder="Enter overtime amount">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Bonus') }}</label>
                                        <input type="number" class="form-control" name="bonus"
                                            placeholder="Enter bonus amount">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Deductions') }}</label>
                                        <input type="number" class="form-control" name="deductions"
                                            placeholder="Enter deductions">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Advance') }}</label>
                                        <input type="number" class="form-control" name="advance"
                                            placeholder="Enter advance amount">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Total Salary') }}</label>
                                        <input type="number" id="totalSalary" class="form-control" name="total_salary"
                                            placeholder="Total salary" readonly>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Salary Month') }}</label>
                                        <select class="form-control form-select select2" name="salary_for" required="">
                                            <option value="" selected disabled>Select Month
                                            </option>
                                            <option value="January">January -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="February">February -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="March">March -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="April">April -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="May">May -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="June">June -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="July">July -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="August">August -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="September">September -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="October">October -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="November">November -
                                                <?php echo date('Y'); ?>
                                            </option>
                                            <option value="December">December -
                                                <?php echo date('Y'); ?>
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('Salary month is required.') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('Note') }}</label>
                                        <textarea class="form-control" name="note" rows="3" placeholder="Enter additional notes"></textarea>
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

                const totalSalary = basicSalary + overTime + bonus - (deductions + advance);

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
