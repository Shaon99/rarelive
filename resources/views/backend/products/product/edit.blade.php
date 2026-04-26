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
                            <form action="{{ route('admin.product.update', $singleProduct->id) }}" method="POST"
                                enctype="multipart/form-data" class="needs-validation" novalidate="">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-3 col-6">
                                        <label class="label" for="code">{{ __('SKU') }}</label>
                                        <div class="input-group">
                                            <input type="text" id="code" name="code" class="form-control"
                                                value="{{ old('code', $singleProduct->code) }}"
                                                placeholder="{{ __('Generate product code') }}">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Product Name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $singleProduct->name) }}" placeholder="Enter product name"
                                            required="">
                                        <div class="invalid-feedback">
                                            {{ __('name can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Brand') }}</label>
                                        <select class="form-control select2" name="brand">
                                            <option value="" selected disabled>{{ __('select brand') }}</option>
                                            @forelse ($brands as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $singleProduct->brand_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Category') }}</label>
                                        <select class="form-control select2" name="category" required="">
                                            <option value="" selected disabled>{{ __('select category') }}</option>
                                            @forelse ($categories as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $singleProduct->category_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}</option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('category can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Unit') }}</label>
                                        <select class="form-control select2" name="unit" required="">
                                            <option value="" selected disabled>{{ __('select unit') }}</option>
                                            @forelse ($units as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $singleProduct->unit_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}</option>
                                            @empty
                                                <option disabled>{{ __('data not found') }}</option>
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ __('unit can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Low Quantity Alert') }}</label>
                                        <input type="number" class="form-control" name="low_quantity_alert"
                                            value="{{ old('low_quantity_alert', $singleProduct->low_quantity_alert) }}"
                                            placeholder="Enter low quantity alert">
                                    </div>

                                    <div class="col-md-3 col-6 form-group">
                                        <label for="discount" class="control-label">{{ __('Discount') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" min="0" step="0.05"
                                                value="{{ old('discount', $singleProduct->discount) }}" name="discount"
                                                id="discount" placeholder="Enter Discount">
                                            <select class="form-control selectric" name="discount_type" id="discount_type">
                                                <option value="fixed"
                                                    {{ $singleProduct->discount_type === 'fixed' ? 'selected' : '' }}>
                                                    {{ __('Fixed') }}</option>
                                                <option value="percentage"
                                                    {{ $singleProduct->discount_type === 'percentage' ? 'selected' : '' }}>
                                                    {{ __('Percentage') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-6 col-md-3">
                                        <label for="dateRangePicker">Select Discount Range</label>
                                        <input type="text" class="form-control" id="dateRangePicker"
                                            name="discount_date_range"
                                            value="{{ old('discount_date_range', $singleProduct->discount_date_range) }}"
                                            placeholder="Select discount date range" autocomplete="off" />
                                    </div>

                                    {{-- <div class="col-md-12 mb-4">
                                        <!-- Product Variation Section -->
                                        <div class="form-check d-flex justify-content-start align-items-center px-0 mb-3">
                                            <div class="row gutters-xs">
                                                <div class="col-auto">
                                                    <label class="colorinput">
                                                        <input name="has_variation" type="checkbox" value="1"
                                                            class="colorinput-input" id="has_variation"
                                                            {{ $singleProduct->has_variation ? 'checked' : '' }} />
                                                        <span class="colorinput-color bg-primary"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <label class="form-check-label ml-2" for="has_variation">This product has
                                                variations</label>
                                        </div>

                                        <div id="variation_section"
                                            class="{{ $singleProduct->has_variation ? '' : 'd-none' }}">
                                            <div class="d-flex justify-content-between my-2">
                                                <h6>Add Product Variations</h6>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    id="addAttributeBtn">+ Add More Attribute</button>
                                            </div>

                                            <div id="attributeGroups">
                                                @if (!empty($attributesData))
                                                    @foreach ($attributesData as $attributeId => $attribute)
                                                        <div class="row attribute-group">
                                                            <!-- Single Select Attribute -->
                                                            <div class="col-md-6">
                                                                <label>Select Attribute</label>
                                                                <select class="form-control select2 attribute-select"
                                                                    name="attributes_data[{{ $loop->index }}][]">
                                                                    <option value="" disabled>Select attribute...
                                                                    </option>
                                                                    @foreach ($allAttributes as $availableAttribute)
                                                                        <option value="{{ $availableAttribute->id }}"
                                                                            {{ $attributeId === $availableAttribute->id ? 'selected' : '' }}
                                                                            data-values='@json($availableAttribute->attributeValues)'>
                                                                            {{ $availableAttribute->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <!-- Multi Select Attribute Values -->
                                                            <div class="col-md-6">
                                                                <label>Select Attribute Values</label>
                                                                <select class="form-control select2 attribute-value-select"
                                                                    name="attribute_values[{{ $loop->index }}][]"
                                                                    multiple>
                                                                    @php
                                                                        $availableAttribute = $allAttributes
                                                                            ->where('id', $attributeId)
                                                                            ->first();
                                                                    @endphp
                                                                    @foreach ($availableAttribute?->values ?? [] as $availableValue)
                                                                        <option value="{{ $availableValue->id }}"
                                                                            {{ in_array((string) $availableValue->id, array_map('strval', array_keys($attribute['values']))) ? 'selected' : '' }}>
                                                                            {{ $availableValue->value }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>


                                                            <!-- Remove button -->
                                                            <div class="col-md-12 text-right my-2">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger remove-attribute-group">
                                                                    Remove Attribute
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="variantTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Attributes</th>
                                                            <th>SKU</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($singleProduct->variations as $key => $variation)
                                                            <tr class="variant-row">
                                                                <!-- Attributes Display -->
                                                                <td>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $variation->variationValues->map(fn($v) => $v->attributeValue->value)->implode(', ') }}"
                                                                        readonly>
                                                                    @foreach ($attributeValuesData[$variation->id]['attributes'] ?? [] as $attrIndex => $attrId)
                                                                        <input type="hidden"
                                                                            name="attributes_data[{{ $key }}][{{ $attrIndex }}]"
                                                                            value="{{ $attrId }}">
                                                                        @foreach ($attributeValuesData[$variation->id]['values'][$attrId] ?? [] as $valueId)
                                                                            <input type="hidden"
                                                                                name="attribute_values[{{ $key }}][{{ $attrIndex }}][]"
                                                                                value="{{ $valueId }}">
                                                                        @endforeach
                                                                    @endforeach
                                                                </td>

                                                                <!-- SKU -->
                                                                <td>
                                                                    <input type="text" class="form-control"
                                                                        name="skus[]" value="{{ $variation->sku }}">
                                                                </td>

                                                                <!-- Remove Variant Button -->
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-variant-row">X</button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <div class="form-group col-md-8 col-12">
                                        <label>{{ __('Description') }}</label>
                                        <textarea class="form-control" rows="12" id="description" name="description">{{ old('description', $singleProduct->description) }}</textarea>
                                    </div>

                                    <div class="form-group col-md-4 col-12 mt-4">
                                        <label class="label" for="image">{{ __('Thumbnail Image') }}</label>
                                        <div class="upload-area">
                                            <div
                                                class="upload-container p-4 border border-2 border-dashed rounded-3 text-center position-relative">
                                                <div class="upload-content">
                                                    <button type="button" data-selection-type="single"
                                                        class="btn btn-secondary mt-2 px-4 imageGallery">
                                                        Browse Files
                                                    </button>
                                                    <p class="selected-file-name mt-2 mb-0 text-muted small"
                                                        id="imageFileInfo">No images selected</p>
                                                    <div id="image-preview" class="img-append"
                                                        style="background-image: url('{{ $singleProduct->image }}')">
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="uploaded_image_url" name="image_url">
                                            <div class="text-center mt-3">
                                                <small class="text-muted d-block">
                                                    <i class="fa fa-info-circle me-1"></i> This image is visible in all
                                                    product box. Minimum
                                                    dimensions required: 195px width X 195px height.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label class="label" for="image">{{ __('Gallery Image') }}</label>
                                            <div class="upload-area">
                                                <div
                                                    class="upload-container p-4 border border-2 border-dashed rounded-3 text-center position-relative">
                                                    <div class="upload-content">
                                                        <button type="button"
                                                            class="btn btn-secondary mt-2 px-4 imageGallery"
                                                            data-selection-type="multiple">
                                                            Browse Files
                                                        </button>
                                                        <p class="selected-file Ascendantly selected-file-name mt-2 mb-0 text-muted small"
                                                            id="galleryFileInfo">No images selected</p>
                                                        <div id="gallery-preview" class="d-flex flex-wrap">
                                                            @foreach ($singleProduct->productGallery as $image)
                                                                <div class="mr-2 mt-3 mb-3"
                                                                    style="width: 100px; height: 100px; position: relative;">
                                                                    <div
                                                                        class="image-container position-relative w-100 h-100">
                                                                        <img src="{{ $image->url }}"
                                                                            class="img-thumbnail w-100 h-100 rounded"
                                                                            style="object-fit: cover;">
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm remove-image position-absolute rounded-circle"
                                                                            style="width: 22px; height: 22px; padding: 0; top: 2px; right: 2px; display: flex; 
                                                                                align-items: center; justify-content: center; border: 1px solid white;"
                                                                            data-url="{{ $image->url }}">
                                                                            <input type="hidden" name="gallery_urls[]"
                                                                                value="{{ $image->url }}">
                                                                            <i class="fas fa-times"
                                                                                style="font-size: 0.6rem;"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right mt-4">
                                    <button class="btn btn-primary btn-icon icon-left btn-md" id="submit-button"
                                        type="submit"><i class="fas fa-save"></i> {{ __('Save Changes') }}</button>
                                </div>
                            </form>
                            @include('backend.ai')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        // Toggle variation section
        $('#has_variation').on('change', function() {
            $('#variation_section').toggleClass('d-none', !this.checked);
        });

        // Add attribute group
        $('#addAttributeBtn').on('click', function() {
            const index = $('.attribute-group').length;
            const attributeGroup = `
                    <div class="row attribute-group">
                        <div class="col-md-6">
                            <label>Select Attribute</label>
                            <select class="form-control select2 attribute-select" name="attributes_data[${index}][]">
                                <option value="" disabled selected>Select attribute...</option>
                                @foreach ($allAttributes as $availableAttribute)
                                    <option value="{{ $availableAttribute->id }}"
                                            data-values='@json($availableAttribute->attributeValues)'>
                                        {{ $availableAttribute->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Select Attribute Values</label>
                            <select class="form-control select2 attribute-value-select" name="attribute_values[${index}][]" multiple>
                            </select>
                        </div>
                        <div class="col-md-12 text-right my-2">
                            <button type="button" class="btn btn-sm btn-danger remove-attribute-group">Remove Attribute</button>
                        </div>
                    </div>`;
            $('#attributeGroups').append(attributeGroup);
            $('.select2').select2();
        });

        // Remove attribute group
        $(document).on('click', '.remove-attribute-group', function() {
            $(this).closest('.attribute-group').remove();
        });

        // Remove variant row
        $(document).on('click', '.remove-variant-row', function() {
            $(this).closest('.variant-row').remove();
        });

        // Update attribute values dropdown when attribute changes
        $(document).on('change', '.attribute-select', function() {
            const $valueSelect = $(this).closest('.attribute-group').find('.attribute-value-select');
            const values = $(this).find('option:selected').data('values') || [];
            $valueSelect.empty();
            values.forEach(value => {
                $valueSelect.append(`<option value="${value.id}">${value.value}</option>`);
            });
            $valueSelect.select2();
        });

        function Random() {
            return Math.floor(Math.random() * 1000000);
        }

        function randomValue() {
            document.getElementById('code').value = 'PRO-' + Random();
        }

        $(document).ready(function() {
            $('.select2').select2();

            // Initialize the date range picker
            var startDate = moment().startOf('day');
            var endDate = moment().endOf('day');
            var currentRange = '{{ old('discount_date_range', $singleProduct->discount_date_range) }}';
            if (currentRange) {
                var dates = currentRange.split(' - ');
                if (dates.length === 2) {
                    startDate = moment(dates[0]);
                    endDate = moment(dates[1]);
                }
            }

            $('#dateRangePicker').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                startDate: startDate,
                endDate: endDate,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    </script>
@endpush

@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.2.0/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea#description',
            menubar: true,
            branding: false,
            plugins: 'code table lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | table',
        });
    </script>
@endpush
