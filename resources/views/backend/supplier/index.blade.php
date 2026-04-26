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
                            @if (auth()->guard('admin')->user()->can('supplier_add'))
                            <a href="{{ route('admin.supplier.create') }}"
                                class="btn btn-primary"><i class="fas fa-plus-circle"></i> {{ __('Create Supplier') }}</a>
                        @endif
                            </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Supplier') }}</th>
                                            <th>{{ __('Contact Person') }}</th>
                                            <th>{{ __('Phone') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Due') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($suppliers as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->contact_person }}</td>
                                                <td>{{ $item->phone }}</td>
                                                <td>{{ $item->email??'N/A' }}</td>
                                                <td>{{ number_format($item->due,2) }} {{ $general->site_currency }}</td>
                                                <td>
                                                    <a href="{{ route('admin.supplierPurchases', $item->id) }}" class="btn btn-success btn-sm mr-1"
                                                        data-toggle="tooltip" title="Purchases">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if (auth()->guard('admin')->user()->can('supplier_edit'))
                                                    <a href="{{ route('admin.supplier.edit', $item->id) }}"
                                                        class="btn btn-primary btn-sm mr-1" data-toggle="tooltip"
                                                        title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    @endif

                                                    @if (auth()->guard('admin')->user()->can('supplier_delete'))
                                                    <button class="btn btn-danger btn-sm delete"
                                                        data-href="{{ route('admin.supplier.destroy', $item->id) }}"
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
