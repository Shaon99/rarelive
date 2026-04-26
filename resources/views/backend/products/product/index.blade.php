@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active">
                        <a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @if (auth()->guard('admin')->user()->can('product_add'))
                                <div class="d-flex">
                                    <button type="button" class="btn btn-icon icon-left btn-info mr-4" data-toggle="modal"
                                        data-target="#importModal" id="openImportModalButton">
                                        <i class="fas fa-file-import"></i> {{ __('Import Products') }}
                                    </button>
                                    <a href="{{ route('admin.product.create') }}"
                                        class="btn btn-icon icon-left btn-primary">
                                        <i class="fas fa-plus-circle"></i> {{ __('Create Product') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <select id="categoryFilter" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-2">
                                    <select id="brandFilter" class="form-control select2">
                                        <option value="">All Brands</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 py-4">
                                    <div class="table-responsive">
                                        <table id="table_2" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#SL</th>
                                                    <th>Action</th>
                                                    <th>Created At</th>
                                                    <th>SKU</th>
                                                    <th>Image</th>
                                                    <th>Name</th>
                                                    <th>Quantity</th>
                                                    @if (auth()->guard('admin')->user()->can('purchases_price_show'))
                                                        <th>Purchase Price</th>
                                                    @endif
                                                    <th>Sale Price</th>
                                                    <th>Branch</th>
                                                    <th>Category</th>
                                                    <th>Brand</th>
                                                    <th>Supplier</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <div id="loading-overlay" class="loading-overlay" style="display: none">
                                                    <div class="loading-overlay-text text-center">please wait...</div>
                                                </div>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
            let table = $('#table_2').DataTable({
                processing: false,
                serverSide: true,
                pagingType: "numbers",
                dom: '<"d-flex justify-content-between align-items-center"<"left-section"l><"right-section"B>>' +
                    '<"d-flex align-items-center search-wrapper"f>' +
                    'rt' +
                    '<"d-flex justify-content-between align-items-center"<"left-info"i><"right-pagination"p>>',
                buttons: [{
                    extend: 'csv',
                    text: '<i class="fa fa-file-csv mr-1"></i> Download CSV',
                    className: 'btn-success py-1 my-1',
                }],
                pageLength: 25,
                lengthMenu: [10, 20, 25, 50, 100],
                language: {
                    lengthMenu: "_MENU_",
                    search: "",
                    searchPlaceholder: "Search records ...",
                },
                initComplete: function() {
                    let searchDebounceTimeout;
                    const searchBox = $('div.dataTables_filter input');
                    searchBox.addClass('form-control mr-5');
                    searchBox.off('input').on('input', function() {
                        const table = $('#table_2').DataTable();
                        clearTimeout(searchDebounceTimeout);
                        searchDebounceTimeout = setTimeout(() => {
                            table.search(this.value).draw();
                        }, 500);
                    });
                },
                ajax: {
                    url: "{{ route('admin.product.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.category = $('#categoryFilter').val();
                        d.brand = $('#brandFilter').val();
                    },
                    "beforeSend": function() {
                        if ($('#loading-overlay').length) {
                            $('#loading-overlay').show();
                        }
                    },
                    "complete": function() {
                        if ($('#loading-overlay').length) {
                            $('#loading-overlay').hide();
                        }
                    }
                },
                columns: [{
                        data: 'sl',
                        name: 'sl',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                    },
                    @if (auth()->guard('admin')->user()->can('purchases_price_show'))
                        {
                            data: 'purchase_price',
                            name: 'purchase_price'
                        },
                    @endif {
                        data: 'sale_price',
                        name: 'sale_price'
                    },
                    {
                        data: 'warehouse',
                        name: 'warehouse',
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                ],
            });

            $('#categoryFilter, #brandFilter').on('change', function() {
                table.ajax.reload();
            });
            // Enable the import modal button and reset import button when modal opens
            $('#importModal').on('show.bs.modal', function() {
                $('#importButton')
                    .removeClass('disabled')
                    .prop('disabled', false)
                    .text('{{ __('Import') }}');

                $('#openImportModalButton') 
                    .removeClass('disabled')
                    .prop('disabled', false);
            });

            // Disable import button and modal trigger on form submit
            $('#importForm').on('submit', function(e) {
                const importButton = $('#importButton');
                importButton
                    .addClass('disabled')
                    .prop('disabled', true)
                    .text('{{ __('Importing...') }}');

                // Disable modal open button to prevent reopening
                $('#openImportModalButton')
                    .addClass('disabled')
                    .prop('disabled', true);
            });

            // File input change visual feedback
            $('#csvFile').on('change', function(e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
                $('#selectedFileName').text(fileName);

                const uploadContainer = $('.upload-container');
                if (e.target.files[0]) {
                    uploadContainer.addClass('border-success').removeClass('border-dashed');
                } else {
                    uploadContainer.removeClass('border-success').addClass('border-dashed');
                }
            });

            // Drag & drop
            const uploadContainer = document.querySelector('.upload-container');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadContainer.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadContainer.addEventListener(eventName, () => {
                    uploadContainer.classList.add('border-primary');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadContainer.addEventListener(eventName, () => {
                    uploadContainer.classList.remove('border-primary');
                }, false);
            });

            uploadContainer.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                const fileInput = document.getElementById('csvFile');
                fileInput.files = files;

                // Trigger change event
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            });
        });
    </script>
@endpush
