@extends('backend.layout.master')
@push('style')
    <style>
        .image-preview-attribute {
            border-radius: 4px;
            max-height: 60px;
            margin-top: 5px;
        }

        .line-height-100 {
            line-height: 16px !important
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
                    <div class="breadcrumb-item active">
                        <a href="{{ route('admin.product.index') }}">{{ __('Products') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data"
                                class="needs-validation" novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-3 col-6">
                                        <label class="label" for="code">{{ __('SKU') }}</label>
                                        <div class="input-group">
                                            <input type="text" id="code" name="code" class="form-control"
                                                value="{{ old('code') }}" placeholder="{{ __('Generate product code') }}"
                                                required="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary"
                                                    id="generate">{{ __('Generate') }}</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}" placeholder="Enter product name" required="">
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Brand') }}</label>
                                        <div class="d-flex">
                                            <select class="form-control select2 flex-grow-1" name="brand">
                                                <option value="" selected disabled> {{ __('Select brand') }}
                                                </option>
                                                @forelse ($brands as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <button type="button" data-href="{{ route('admin.brand.store') }}"
                                                data-name="Brand" class="btn btn-primary create ml-1">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label for="category">{{ __('Category') }}</label>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <select id="category" class="form-control select2" name="category"
                                                    required="">
                                                    <option value="" selected disabled>{{ __('Select category...') }}
                                                    </option>
                                                    @foreach ($categories as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    {{ __('Category cannot be empty') }}
                                                </div>
                                            </div>
                                            <button type="button" data-href="{{ route('admin.category.store') }}"
                                                data-name="Category" class="btn btn-primary ml-1 create">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Unit') }}</label>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <select class="form-control select2 flex-grow-1" name="unit"
                                                    required="">
                                                    <option value="" selected disabled>{{ __('select unit...') }}
                                                    </option>
                                                    @forelse ($units as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @empty
                                                    @endforelse
                                                </select>
                                                <div class="invalid-feedback">
                                                    {{ __('Unit cannot be empty') }}
                                                </div>
                                            </div>
                                            <button type="button" data-href="{{ route('admin.unit.store') }}"
                                                data-name="Unit" class="btn btn-primary ml-1 create"><i
                                                    class="fa fa-plus"></i></button>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3 col-6">
                                        <label>{{ __('Low Quantity Alert') }}</label>
                                        <input type="number" class="form-control" name="low_quantity_alert"
                                            value="{{ old('low_quantity_alert') }}" placeholder="Enter low quantity alert">
                                    </div>

                                    <div class="col-md-3 col-6 form-group">
                                        <label for="discount" class="control-label">{{ __('Discount') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" min="0" step="0.05"
                                                value="0" name="discount" id="discount" placeholder="Enter Discount">
                                            <select class="form-control selectric" name="discount_type" id="discount_type">
                                                <option value="fixed">{{ __('Flat Amount') }}</option>
                                                <option value="percentage">{{ __('Percentage') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-6 col-md-3">
                                        <label for="dateRangePicker">Select Discount Range</label>
                                        <input type="text" class="form-control" id="dateRangePicker"
                                            name="discount_date_range" placeholder="Select discount date range"
                                            autocomplete="off" />
                                    </div>

                                    {{-- <div class="col-md-12 mb-4">
                                        <!-- Product Variation Section -->
                                        <div class="form-check d-flex justify-content-start align-items-center px-0 mb-3">
                                            <div class="row gutters-xs">
                                                <div class="col-auto">
                                                    <label class="colorinput">
                                                        <input name="has_variation" type="checkbox" value="1"
                                                            class="colorinput-input" id="has_variation" />
                                                        <span class="colorinput-color bg-primary"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <label class="form-check-label ml-2" for="has_variation">This product has
                                                variations</label>
                                        </div>

                                        <div id="variation_section" style="display: none;">
                                            <div class="d-flex justify-content-between my-2">
                                                <h6>Add Product Variations</h6>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    id="addAttributeBtn">+ Add More Attribute</button>
                                            </div>

                                            <div id="attributeGroups">
                                                <div class="row attribute-group">
                                                    <div class="col-md-6">
                                                        <label>Select Attribute</label>
                                                        <select class="form-control select2 attribute-select"
                                                            name="attributes_data_group[0][]">
                                                            <option value="" selected disabled>Select attributes...
                                                            </option>
                                                            @foreach ($productAttributes as $item)
                                                                <option value="{{ $item->id }}"
                                                                    data-values='@json($item->values)'>
                                                                    {{ $item->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Select Attribute Values</label>
                                                        <select class="form-control select2 attribute-value-select"
                                                            name="attribute_values_group[0][]" multiple></select>
                                                    </div>
                                                    <div class="col-md-12 text-right my-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-attribute-group">Remove
                                                            Attribute</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="variantTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Variant</th>
                                                            <th>SKU</th>
                                                            <th>Photo</th>
                                                            <th>Preview</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <div class="form-group col-md-8 col-12 mt-4">
                                        <label>{{ __('Description') }}</label>
                                        <textarea class="form-control" rows="12" id="description" name="description" placeholder="Type here...">{{ old('description') }}</textarea>
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
                                                    <div id="image-preview">
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
                                                        <div id="gallery-preview" class="d-flex flex-wrap"></div>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-3">
                                                    <small class="text-muted d-block">
                                                        <i class="fa fa-info-circle me-1"></i> This image is visible in
                                                        product details page. Minimum dimensions required: 195px width X
                                                        195px height.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right mt-4">
                                    <button class="btn btn-primary btn-icon icon-left btn-md" id="submit-button"
                                        type="submit"><i class="fas fa-save"></i>
                                        {{ __('Create a new product') }}</button>
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

        function Random() {
            let uniqueId = Math.floor(Math.random() * 1000);
            return 'SKU-' + uniqueId;
        }

        function randomValue() {
            document.getElementById('code').value = Random();
        }

        $("#generate").click(function() {
            randomValue();
        });

        $(document).ready(function() {
            // Initialize the date range picker
            var startDate = moment().startOf('day');
            var endDate = moment().endOf('day');

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
        });

        $(document).ready(function() {
            let groupIndex = {{ !empty($attributesData) ? count($attributesData) - 1 : 0 }};

            function getCombinations(arr) {
                if (arr.length === 0) return [];
                return arr.reduce((acc, val) => {
                    let res = [];
                    acc.forEach(a => {
                        val.forEach(v => {
                            res.push(a.concat([v]));
                        });
                    });
                    return res;
                }, [
                    []
                ]).filter(comb => comb.length > 0);
            }

            function generateVariantTable() {
                let groups = $('.attribute-group');
                let attributeValues = [];
                let attributeIds = [];
                let attributeNames = [];

                // Collect selected attributes and values
                groups.each(function(index) {
                    let attrId = $(this).find('.attribute-select').val();
                    let values = $(this).find('.attribute-value-select').val() || [];
                    let attrName = $(this).find('.attribute-select option:selected').text();

                    if (attrId && values.length > 0) {
                        attributeValues.push(values);
                        attributeIds.push(attrId);
                        attributeNames.push(attrName);
                    }
                });

                if (attributeValues.length === 0) {
                    $('#variantTable tbody').empty();
                    return;
                }

                let combinations = getCombinations(attributeValues);
                let tbody = $('#variantTable tbody');
                tbody.empty();

                combinations.forEach((comb, index) => {
                    let variantParts = [];
                    let variantAttributeIds = [];
                    let variantAttributeValueIds = [];

                    // Build variant name and collect IDs
                    comb.forEach((valId, i) => {
                        let attrName = attributeNames[i];
                        let valText = $(`.attribute-value-select option[value="${valId}"]`).text();
                        variantParts.push(valText);
                        variantAttributeIds.push(attributeIds[i]);
                        variantAttributeValueIds.push(valId);
                    });

                    let variantName = variantParts.join(' ');
                    let sku = generateSKU(variantParts);

                    // Create hidden inputs for attributes_data and attribute_values
                    let attributeInputs = '';
                    let valueInputs = '';
                    groups.each(function(groupIndex) {
                        let attrId = $(this).find('.attribute-select').val();
                        let values = $(this).find('.attribute-value-select').val() || [];
                        if (attrId && values.length > 0) {
                            // Include all selected values for this attribute in the variant
                            attributeInputs +=
                                `<input type="hidden" name="attributes_data[${index}][${groupIndex}]" value="${attrId}">`;
                            values.forEach(valId => {
                                if (comb.includes(valId)) {
                                    valueInputs +=
                                        `<input type="hidden" name="attribute_values[${index}][${groupIndex}][]" value="${valId}">`;
                                }
                            });
                        }
                    });

                    let row = `
                        <tr>
                            <td><input type="text" class="form-control" name="variants[]" value="${variantName}" readonly></td>
                            <td><input type="text" class="form-control" name="skus[]" value="${sku}" readonly></td>
                            <td>
                                <input type="file" name="photos[${index}]" class="form-control image-input line-height-100" accept="image/*">
                            </td>
                            <td class="image-preview-cell">
                                <img src="" class="image-preview-attribute" style="max-width: 60px; display: none;">
                            </td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-variant-row">X</button></td>
                            ${attributeInputs}
                            ${valueInputs}
                        </tr>
                    `;
                    tbody.append(row);
                });
            }

            function initializeVariantTable() {
                // No changes needed for create form; kept for edit form compatibility
            }

            function updateDisabledAttributes() {
                let selected = [];
                $('.attribute-group .attribute-select').each(function() {
                    let val = $(this).val();
                    if (val) selected.push(val);
                });

                $('.attribute-select').each(function() {
                    let current = $(this).val();
                    $(this).find('option').each(function() {
                        let optVal = $(this).val();
                        if (optVal === current || optVal === '') {
                            $(this).prop('disabled', false);
                        } else {
                            $(this).prop('disabled', selected.includes(optVal));
                        }
                    });
                });
            }

            $('.select2').select2();

            $('#has_variation').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#variation_section').slideDown();
                } else {
                    $('#variation_section').slideUp();
                    $('#variantTable tbody').empty();
                    $('#attributeGroups').html('');
                    groupIndex = 0;
                }
            });

            $(document).on('change', '.attribute-select', function() {
                const values = $(this).find('option:selected').data('values') || [];
                const valueSelect = $(this).closest('.attribute-group').find('.attribute-value-select');
                valueSelect.empty();

                values.forEach(val => {
                    valueSelect.append(`<option value="${val.id}">${val.value}</option>`);
                });

                valueSelect.trigger('change');
                updateDisabledAttributes();
            });

            $(document).on('change', '.attribute-value-select', function() {
                generateVariantTable();
            });

            $('#addAttributeBtn').on('click', function() {
                groupIndex++;
                let newGroup = `
                    <div class="row attribute-group mt-3">
                        <div class="col-md-6">
                            <label>Select Attribute</label>
                            <select class="form-control select2 attribute-select" name="attributes_data_group[${groupIndex}][]">
                                <option value="" selected disabled>Select attributes...</option>
                                @foreach ($productAttributes as $item)
                                    <option value="{{ $item->id }}" data-values='@json($item->values)'>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Select Attribute Values</label>
                            <select class="form-control select2 attribute-value-select" multiple name="attribute_values_group[${groupIndex}][]"></select>
                        </div>
                        <div class="col-md-12 text-right my-2">
                            <button type="button" class="btn btn-sm btn-danger remove-attribute-group">Remove Attribute</button>
                        </div>
                    </div>
                `;
                $('#attributeGroups').append(newGroup);
                $('.select2').select2();
                updateDisabledAttributes();
            });

            $(document).on('click', '.remove-attribute-group', function() {
                $(this).closest('.attribute-group').remove();
                updateDisabledAttributes();
                generateVariantTable();
            });

            $(document).on('click', '.remove-variant-row', function() {
                $(this).closest('tr').remove();
            });

            function generateSKU(combination) {
                const base = combination.join('-').toLowerCase();
                const timestamp = Date.now().toString().slice(-4);
                return `${base}-${timestamp}`;
            }

            $(document).on('change', '.image-input', function() {
                const file = this.files[0];
                const preview = $(this).closest('tr').find('.image-preview-attribute');
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.hide();
                }
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
