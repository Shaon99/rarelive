@extends('backend.layout.master')
@push('style')
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #676767 !important;
            line-height: 40px !important;
            text-transform: capitalize !important;
            font-size: 12px !important;
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
                            <!-- Combo Product Update Form -->
                            <form action="{{ route('admin.comboProduct.update', $combo->id) }}" method="POST"
                                enctype="multipart/form-data" class="needs-validation" novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 col-4 mb-3">
                                        <label>{{ __('Name') }}</label>
                                        <input type="text" class="form-control" id="comboName" name="name"
                                            value="{{ old('name', $combo->name) }}" placeholder="Enter combo name" required>
                                    </div>

                                    <div class="col-md-4 col-4 mb-3">
                                        <label>{{ __('Price') }}</label>
                                        <input type="text" class="form-control" id="price" name="price"
                                            value="{{ old('price',$combo->price) }}" placeholder="Enter price" required="">
                                    </div>

                                    <div class="col-md-4 col-4 mb-3">
                                        <label>{{ __('Quantity') }}</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity"
                                            value="{{ old('quantity',$combo->quantity) }}" placeholder="Enter quantity" required="">
                                    </div>

                                    <div class="col-md-12">
                                        <!-- Products Section -->
                                        <div id="products-section" class="border rounded p-3">
                                            <h6 class="mb-3">Products</h6>
                                            @foreach ($combo->products as $index => $product)
                                                <div class="row mb-3 align-items-center product-row">
                                                    <div class="col-md-6">
                                                        <select name="products[{{ $index }}][id]"
                                                            class="form-control select2" required>
                                                            <option value="" disabled>Select a product...</option>
                                                            @foreach ($products as $availableProduct)
                                                                <option value="{{ $availableProduct->id }}"
                                                                    {{ $availableProduct->id == $product->id ? 'selected' : '' }}>
                                                                    {{ $availableProduct->name }} (SP:
                                                                    {{ $availableProduct->sale_price }} Q:
                                                                    {{ $availableProduct->quantity }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number"
                                                            name="products[{{ $index }}][quantity]"
                                                            class="form-control" placeholder="Quantity" min="1"
                                                            value="{{ $product->pivot->quantity }}" required>
                                                    </div>
                                                    <div class="col-md-2 text-end">                                                        
                                                        <button type="button"
                                                            {{ route('admin.comboProduct.delete', $combo->id) }}
                                                            class="btn btn-danger delete"><i
                                                                class="fa fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <button type="button" class="mb-2 btn btn-success add-product"><i
                                                class="fa fa-plus"></i></button>
                                        </div>
                                        <!-- Submit Button -->
                                        <div class="mt-4 d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">Update Combo Product</button>
                                        </div>
                                    </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the product index with the number of existing products
            let productIndex = {{ $combo->products->count() }};

            // Initialize Select2 for existing dropdowns
            $('.select2').select2({
                placeholder: 'Select a product...',
                allowClear: true
            });

            // Add Product Row
            document.querySelector('.add-product').addEventListener('click', function() {
                const productsSection = document.getElementById('products-section');
                const newRow = document.createElement('div');
                newRow.className = 'row mb-3 align-items-center product-row';

                newRow.innerHTML = `
            <div class="col-md-6">
                <select name="products[${productIndex}][id]" class="form-control select2" required>
                    <option value="" selected disabled>Select a product...</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (SP: {{ $product->sale_price }} Q: {{ $product->quantity }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="products[${productIndex}][quantity]" class="form-control" placeholder="Quantity" min="1" required>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-danger remove-product"><i class="fa fa-trash"></i></button>
            </div>
        `;

                productsSection.appendChild(newRow);

                // Initialize Select2 for new dropdown
                $(newRow).find('.select2').select2({
                    placeholder: 'Select a product...',
                    allowClear: true
                });

                // Add remove functionality to the new row
                newRow.querySelector('.remove-product').addEventListener('click', function() {
                    newRow.remove();
                });

                productIndex++;
            });

            // Attach delete functionality to existing rows
            document.querySelectorAll('.remove-product').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.product-row').remove();
                });
            });
        });
    </script>
@endpush
