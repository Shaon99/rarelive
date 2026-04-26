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
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Product Name') }}</th>
                                            <th>{{ __('Start Stock') }}</th>
                                            <th>{{ __('Transfer Stock') }}</th>
                                            <th>{{ __('Remain Stock') }}</th>
                                            <th>{{ __('Transfer Date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($stockHistory as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ @$item->product->name }}</td>
                                                <td>{{ @$item->old_purchases }}</td>
                                                <td>{{ $item->transfer_purchases }}</td>
                                                <td>{{ @$item->remain_purchases }}</td>
                                                <td>{{ @$item->created_at->format('d-m-Y') }}</td>

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
