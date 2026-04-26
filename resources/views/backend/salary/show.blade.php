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
                    <div class="card p-5 printable-area">
                        <div class="text-right mb-4">
                            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
                        </div>
                        <div class="invoice-header">
                            <h2>Salary Invoice</h2>
                            <p class="mb-0">Invoice Date: {{ \Carbon\Carbon::now()->format('d M, Y') }}</p>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Employee Details</h4>
                                <p><strong>Name:</strong> {{ $salary->employee->employee_name }}</p>
                                <p><strong>Salary For:</strong> {{ \Carbon\Carbon::parse($salary->salary_for)->format('F, Y') }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h4>Status</h4>
                                @php
                                    $status = $salary->status;

                                    if ($status == 'paid') {
                                        $badgeClass = 'success';
                                        $statusText = 'Paid';
                                    } elseif ($status == 'partial') {
                                        $badgeClass = 'warning';
                                        $statusText = 'Partially Paid';
                                    } else {
                                        $badgeClass = 'danger';
                                        $statusText = 'Due';
                                    }
                                @endphp
                                <p><span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span></p>
                            </div>
                        </div>
                        <hr>
                        <h4>Salary Details</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Basic Salary</th>
                                    <th>Others</th>
                                    <th>Over Time</th>
                                    <th>Bonus</th>
                                    <th>Deductions</th>
                                    <th>Advance</th>
                                    <th>Total Salary</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ currency_format($salary->basic_salary) }}</td>
                                    <td>{{ currency_format($salary->others) }}</td>
                                    <td>{{ currency_format($salary->over_time) }}</td>
                                    <td>{{ currency_format($salary->bonus) }}</td>
                                    <td>{{ currency_format($salary->deductions) }}</td>
                                    <td>{{ currency_format($salary->advance) }}</td>
                                    <td>{{ currency_format($salary->total_salary) }}</td>
                                    <td>{{ currency_format($salary->payments->sum('amount_paid')) }}</td>
                                    <td>{{ currency_format($salary->total_salary - $salary->payments->sum('amount_paid')) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <h4>Payment History</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Paid Amount</th>
                                    <th>Payment Method</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salary->payments as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('d M, Y h:i A') }}</td>
                                        <td>{{ currency_format($item->amount_paid) }}</td>
                                        <td><span class="badge badge-success">{{ $item->paymentMethod->account_name??'N/A' }}</span></td>
                                        <td>{{ $item->note??'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center">{{ __('No record available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="text-center mt-4">
                            <p>Thank you for your hard work!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('style')
    <style>
        .printable-area {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-header h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .invoice-header p {
            margin: 5px 0;
            color: #777;
        }
        .table-bordered {
            border: 1px solid #ddd;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .printable-area, .printable-area * {
                visibility: visible;
            }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .badge {
                padding: 5px 10px;
                border-radius: 3px;
            }
            .badge-success {
                background-color: #28a745;
                color: #fff;
            }
            .badge-warning {
                background-color: #ffc107;
                color: #000;
            }
            .badge-danger {
                background-color: #dc3545;
                color: #fff;
            }
        }
    </style>
@endsection