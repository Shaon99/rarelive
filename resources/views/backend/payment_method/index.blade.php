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
                            <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary"><i
                                    class="fas fa-plus-circle"></i>
                                {{ __('Create Account') }}</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Account Type') }}</th>
                                            <th>{{ __('Account Name') }}</th>
                                            <th>{{ __('Account Number') }}</th>
                                            <th>{{ __('Branch Name') }}</th>
                                            <th>{{ __('Account Holder') }}</th>
                                            <th>{{ __('Current Balance') }}</th>
                                            <th>{{ __('Phone') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalBalance = 0;
                                        @endphp

                                        @forelse ($paymentMethods as $item)
                                            @php
                                                $isSteadfast =
                                                    $item->type === \App\Constants\CommonConstant::STEADFAST;
                                                $currentBalance = $isSteadfast
                                                    ? $steadfast_current_balance
                                                    : $item->current_balance;
                                                $totalBalance += $currentBalance;
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->type }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->account_number ?? 'N/A' }}</td>
                                                <td>{{ $item->branch_name ?? 'N/A' }}</td>
                                                <td>{{ $item->account_name ?? 'N/A' }}</td>
                                                <td>{{ currency_format($currentBalance) }}</td>
                                                <td>{{ $item->phone ?? 'N/A' }}</td>
                                                <td>{{ $item->address ?? 'N/A' }}</td>
                                                <td>{{ $item->created_at->format('d M, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.accounts.edit', $item->id) }}"
                                                        class="btn btn-primary btn-sm" data-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6" class="text-right">{{ __('Total') }}</th>
                                            <th>{{ currency_format($totalBalance) }}</th>
                                            <th colspan="4"></th>
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
