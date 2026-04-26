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
                            @if (auth()->guard('admin')->user()->can('combo_product_add'))
                            <a href="{{ route('admin.comboProduct.create') }}" class="btn btn-icon icon-left btn-primary"><i
                                    class="fas fa-plus-circle"></i> {{ __('Create Combo Product') }}</a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Name</th>
                                            <th>Sell Price</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($combos as $item)
                                            <tr>
                                                <td>{{ @$loop->iteration }}</td>
                                                <td>{{ @$item->name }}</td>
                                                <td>{{ @$item->price }}</td>
                                                <td>{{ @$item->quantity }}</td>
                                                <td>
                                                    <a href="{{ route('admin.comboProduct.view', $item->id) }}"
                                                        class="btn btn-success btn-sm mr-1" data-toggle="tooltip"
                                                        title="details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if (auth()->guard('admin')->user()->can('combo_product_edit'))
                                                    <a href="{{ route('admin.comboProduct.edit', $item->id) }}"
                                                        class="btn btn-primary btn-sm mr-1" data-toggle="tooltip"
                                                        title="edit">
                                                        <i class="fas fa-pencil"></i>
                                                    </a>
                                                    @endif
                                                    @if (auth()->guard('admin')->user()->can('combo_product_delete'))
                                                    <button class="btn btn-danger btn-sm delete"
                                                        data-href="{{ route('admin.comboProduct.delete', $item->id) }}"
                                                        data-toggle="tooltip" title="Delete" type="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endif
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
