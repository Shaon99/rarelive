@extends('backend.layout.master')
@push('style')
    <style>
        .view-card {
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px !important;
            color: #000 !important;
            border-radius: 10px;
            text-align: center;
            padding: 2px
        }
    </style>
@endpush
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
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card view-card">
                                            <p class="mb-1">{{ __('Name') }}</p>
                                            <h6>{{ $combo->name }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card view-card">
                                            <p class="mb-1">{{ __('Price') }}</p>
                                            <h6>{{ $combo->price }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        @if ($combo->products && count($combo->products) > 0)
                                        {{-- @dd($combo->products) --}}
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>SL</th>
                                                        <th>Product Name</th>
                                                        <th>Sales Price</th>
                                                        <th>Quantity</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($combo->products as $index => $product)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $product->name }}</td>
                                                            <td>{{ number_format($product->sale_price,2) }}</td>
                                                            <td>{{ $product->pivot->quantity }}</td>
                                                            <td>{{ number_format($product->pivot->quantity * $product->sale_price,2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p>No products available in this combo.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
