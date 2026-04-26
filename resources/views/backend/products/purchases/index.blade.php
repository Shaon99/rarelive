@extends('backend.layout.master')


@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>
                    @if (request()->has('duepurchases'))
                        {{ __('Due - ') }}
                    @endif {{ __($pageTitle) }}
                </h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        @if (request()->has('duepurchases'))
                            {{ __('Due - ') }}
                        @endif {{ __($pageTitle) }}
                    </div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            @if (auth()->guard('admin')->user()->can('purchase_add'))
                                <a href="{{ route('admin.purchases.create') }}"
                                    class="btn btn-icon icon-left btn-primary"><i class="fas fa-plus-circle"></i>
                                    {{ __('Create Purchases') }}</a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_2" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Action') }}</th>
                                            <th>{{ __('Created By') }}</th>
                                            <th>{{ __('Reference no') }}</th>
                                            <th>{{ __('Invoice no') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Warehouse') }}</th>
                                            <th>{{ __('Supplier') }}</th>
                                            <th>{{ __('Grand Total') }}</th>
                                            <th>{{ __('Paid Amount') }}</th>
                                            <th>{{ __('Due Amount') }}</th>
                                            <th>{{ __('Payment Status') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($allPurchases as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>                                                    
                                                    @if (auth()->guard('admin')->user()->can('due_purchase_payment'))
                                                        @if ($item->due_amount)
                                                            <button class="btn  btn-info due btn-sm btn-icon mr-1"
                                                                data-href="{{ route('admin.purchases.duepayment', $item->id) }}"
                                                                data-due="{{ $item->due_amount }}"
                                                                data-name="{{ $item->invoice_no . ' - Due payment' }}"
                                                                data-toggle="tooltip" title="Due Payment" type="button">
                                                                <i class="fas fa-credit-card"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                    @if (auth()->guard('admin')->user()->can('purchases_list'))
                                                    <a href="{{ route('admin.purchases.show', $item->id) }}"
                                                        class="btn btn-success btn-sm btn-icon mr-1" data-toggle="tooltip"
                                                        title="Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @endif
                                                    {{-- @if (auth()->guard('admin')->user()->can('purchase_edit'))
                                                    <a href="{{ route('admin.purchases.edit', $item->id) }}"
                                                        class="btn btn-primary btn-sm btn-icon mr-1" data-toggle="tooltip"
                                                        title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    @endif
                                                    @if (auth()->guard('admin')->user()->can('purchase_delete'))
                                                    <button class="btn btn-danger delete btn-sm btn-icon"
                                                        data-href="{{ route('admin.purchases.destroy', $item->id) }}"
                                                        data-toggle="tooltip" title="Delete" type="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endif --}}
                                                </td>
                                                <td>{{ $item->createdBy->name??'N/A' }}</td>
                                                <td>{{ $item->reference_no }}</td>
                                                <td>{{ $item->invoice_no }}</td>
                                                <td>{{ $item->purchase_date }}</td>
                                                <td>{{ $item->warehouse->name??'N/A' }}</td>
                                                <td>{{ $item->supplier->name??'N/A' }}</td>
                                                <td>{{ number_format($item->grand_total, 2) . ' ' . @$general->site_currency }}
                                                </td>
                                                <td>{{ number_format($item->paid_amount, 2) . ' ' . @$general->site_currency }}
                                                </td>
                                                <td>{{ number_format($item->due_amount, 2) . ' ' . @$general->site_currency }}
                                                </td>
                                                <td>
                                                    @if ($item->payment_status == 0)
                                                        <span class="badge badge-danger">{{ __('Due') }}</span>
                                                    @elseif($item->payment_status == 1)
                                                        <span class="badge badge-success">{{ __('Completed') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->created_at->format('d M, Y') }}</td>
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

@push('script')
    <script>
        $(document).ready(function() {
            $('#table_2').DataTable({
                pagingType: "numbers",
            });
        });
    </script>
@endpush
