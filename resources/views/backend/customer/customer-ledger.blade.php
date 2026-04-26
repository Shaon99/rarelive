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
                <div class="col-lg-4 col-md-4 col-sm-12 col-6 mb-4">
                    <div class="metric-card d-flex justify-content-between align-items-center">
                        <div>
                            <div class="metric-value total_sales_draft fs-4 fw-bold">
                                {{ currency_format(customerDue($customerP->id)) }}
                            </div>
                            <div class="metric-label">{{ __('Current Due') }}</div>
                        </div>
                        <div class="metric-icon">
                            <i class="fas fa-solid fa-spinner fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-end">
                            <a href="{{ route('admin.customer.index') }}" class="btn btn-primary" data-toggle="tooltip"
                                title="customers">
                                <i class="fas fa-list"></i> {{ __('Customers') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Note</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $balance = 0;
                                            $totalDebit = 0;
                                            $totalCredit = 0;
                                        @endphp
                                        @foreach ($ledger as $entry)
                                            @php
                                                $debit = $entry->debit ? (float) $entry->amount : 0;
                                                $credit = $entry->credit ? (float) $entry->amount : 0;
                                                $balance += $debit - $credit;
                                                $totalDebit += $debit;
                                                $totalCredit += $credit;
                                            @endphp
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d M Y h:i A') }}
                                                </td>
                                                <td>{{ Str::headline($entry->transaction_type) }}</td>
                                                <td>{{ $entry->note }}</td>
                                                <td>{{ $debit ? currency_format($debit) : '-' }}</td>
                                                <td>{{ $credit ? currency_format($credit) : '-' }}</td>
                                                <td>{{ currency_format($balance) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="3" class="text-right">Total</td>
                                            <td>{{ currency_format($totalDebit) }}</td>
                                            <td>{{ currency_format($totalCredit) }}</td>
                                            <td>{{ currency_format($balance) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
