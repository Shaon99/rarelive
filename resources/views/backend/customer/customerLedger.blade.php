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
                        <div class="card-header d-flex justify-content-end">
                            <a href="{{ route('admin.customer.index') }}" class="btn btn-primary" data-toggle="tooltip"
                                title="customers">
                                <i class="fas fa-list"></i>  {{ __('Customers') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Invoice no') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('GrandTotal') }}</th>
                                            <th>{{ __('Paid Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('View Invoice') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customerLedger as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->invoice_no }}</td>
                                                <td>{{ $item->created_at->format('d/m/y') }}</td>
                                                <td>{{ number_format($item->grand_total, 2) . ' ' . @$general->site_currency }}
                                                </td>
                                                <td>{{ number_format($item->paid_amount, 2) . ' ' . @$general->site_currency }}
                                                </td>
                                                <td>
                                                    @if ($item->system_status === 'pending')
                                                        <span class="badge badge-info">{{ __('Shipping') }}</span>
                                                    @elseif($item->system_status === 'completed')
                                                        <span class="badge badge-success">{{ __('Delivered') }}</span>
                                                    @elseif($item->system_status === 'cancelled')
                                                        <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ route('admin.invoice', $item->id) }}"
                                                            class="btn btn-success btn-action mr-1" data-toggle="tooltip"
                                                            title="invoice">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
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
