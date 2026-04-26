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
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Reference No') }}</th>
                                            <th>{{ __('Invoice No') }}</th>
                                            <th>{{ __('Grand Total') }}</th>
                                            <th>{{ __('Paid Amount') }}</th>
                                            <th>{{ __('Due Amount') }}</th>
                                            <th>{{ __('Payment Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($suppliers->purchases as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->created_at->format('d M, Y H:i A') }}</td>
                                                <td>{{ $item->reference_no }}</td>
                                                <td>{{ $item->invoice_no }}</td>
                                                <td>{{ number_format($item->grand_total, 2) }}</td>
                                                <td>{{ number_format($item->paid_amount, 2) }}
                                                </td>
                                                <td>{{ number_format($item->due_amount, 2) }}
                                                </td>
                                                <td>
                                                    @if ($item->payment_status == 0)
                                                        <span class="badge badge-danger">{{ __('Due') }}</span>
                                                    @elseif($item->payment_status == 1)
                                                        <span class="badge badge-success">{{ __('Completed') }}</span>
                                                    @endif
                                                </td>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.purchases.show', $item->id) }}" class="btn btn-success btn-sm mr-1"
                                                        data-toggle="tooltip" title="Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
