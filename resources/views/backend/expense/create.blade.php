@extends('backend.layout.master')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.product.index') }}">{{ __('Products') }}</a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.expense.store') }}" method="POST" class="needs-validation"
                                novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('Expense Categories & Amounts') }}</label>
                                        <div id="expense-items">
                                            <div class="d-flex mb-2">
                                                <select class="form-control select2 flex-grow-1 mr-2"
                                                    name="expenseCategories[]" required>
                                                    <option value="" selected disabled>
                                                        {{ __('Select Expense Category') }}</option>
                                                    @forelse ($expenseCategories as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @empty
                                                        <option disabled>{{ __('No record found') }}</option>
                                                    @endforelse
                                                </select>
                                                <input type="number" name="amounts[]" class="form-control mx-2"
                                                    placeholder="Amount" required>
                                                <button type="button" class="btn btn-danger btn-sm remove-expense-row"><i
                                                        class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <button type="button" id="add-expense-row" class="btn btn-sm btn-success mt-2"><i
                                                class="fa fa-plus"></i> Add More</button>
                                    </div>


                                    <div class="form-group col-md-4">
                                        <label>{{ __('Date') }}</label>
                                        <input type="text" name="date" class="form-control datepicker" required="">
                                        <div class="invalid-feedback">
                                            {{ __('date can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-4">
                                        <label>{{ __('Payment Account') }}</label>
                                        <select class="form-control select2" name="payment_account" required="">
                                            <option value="" selected disabled>{{ __('select Payment Account') }}
                                            </option>
                                            @forelse ($payment_account as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }} @if ($item->account_number)
                                                        (AC: {{ $item->account_number }})
                                                    @endif
                                                </option>
                                            @empty
                                                <option disabled>{{ __('No record found') }}
                                                </option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('Please Add a payment account') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Remark') }}</label>
                                        <textarea class="form-control" rows="1" value="{{ old('note') }}" name="note" placeholder="Type here..."></textarea>
                                    </div>

                                </div>
                                <div class="text-right py-4">
                                    <button class="btn btn-primary" type="submit">{{ __('Create Expense') }}</button>
                                </div>
                            </form>
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
            // Add new expense row
            $('#add-expense-row').on('click', function() {
                let newRow = `
                <div class="d-flex mb-2">
                    <select class="form-control select2 flex-grow-1 mr-2" name="expenseCategories[]" required>
                        <option value="" selected disabled>Select Expense Category</option>
                        @foreach ($expenseCategories as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="amounts[]" class="form-control mx-2" placeholder="Amount" required>
                    <button type="button" class="btn btn-danger btn-sm remove-expense-row">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `;

                $('#expense-items').append(newRow);

                // Re-initialize select2 for new elements
                $('#expense-items .select2').select2();
            });

            // Remove expense row
            $(document).on('click', '.remove-expense-row', function() {
                $(this).closest('.d-flex').remove();
            });
        });
    </script>
@endpush
