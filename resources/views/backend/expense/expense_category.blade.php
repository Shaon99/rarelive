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
                        <div class="card-header d-flex justify-content-between">
                            @if (auth()->guard('admin')->user()->can('expense_category_add'))
                            <button data-href="{{ route('admin.expense-category.store') }}" data-name="Expense Category"
                                class="btn btn-primary create"><i class="fas fa-plus-circle"></i> {{ __('Create Expense Category') }}</button>
                                @endif
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Expense Category Name') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($expenseCategories as $item)
                                            <tr>
                                                <td>{{ @$loop->iteration }}</td>
                                                <td>{{ @$item->name }}</td>
                                                <td>{{ @$item->created_at->format('d M, Y H:i A') }}</td>
                                                <td>
                                                    @if (auth()->guard('admin')->user()->can('expense_category_edit'))
                                                    <button class="btn btn-primary btn-sm edit mr-1"
                                                        data-href="{{ route('admin.expense-category.update', $item->id) }}"
                                                        data-item="{{ $item->name }}" data-name="expense-category"
                                                        data-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    @endif
                                                    @if (auth()->guard('admin')->user()->can('expense_category_delete'))
                                                    <button class="btn btn-danger btn-sm delete"
                                                        data-href="{{ route('admin.expense-category.destroy', $item->id) }}"
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
