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
                        <div class="card-header">
                            @if (auth()->guard('admin')->user()->can('attribute_add'))
                                <button class="btn btn-primary" data-toggle="modal" data-target="#attributeModal"><i
                                        class="fas fa-plus-circle"></i>
                                    {{ __('Create Attribute') }}</button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table text-capitalize">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Product Attribute Name') }}</th>
                                            <th>{{ __('Product Attribute Value') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($attributes as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>
                                                    {{ $item->values->pluck('value')->join(', ') }}
                                                </td>
                                                <td>{{ $item->created_at->format('d M, Y H:i A') }}</td>
                                                <td>
                                                    @if (auth()->guard('admin')->user()->can('attribute_edit'))
                                                        <button class="btn btn-primary btn-sm editAttributeBtn"
                                                            data-href="{{ route('admin.productAttributeUpdate', $item->id) }}"
                                                            data-attribute='@json($item)' title="Edit">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </button>
                                                    @endif

                                                    @if (auth()->guard('admin')->user()->can('attribute_delete'))
                                                        <button class="btn btn-danger delete btn-sm"
                                                            data-href="{{ route('admin.productAttributeDelete', $item->id) }}"
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

    <!-- Modal -->
    <div class="modal fade" id="attributeModal" tabindex="-1" aria-labelledby="attributeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="attributeForm" method="POST" action="{{ route('admin.productAttributeStore') }}"
                class="needs-validation" novalidate="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attributeModalLabel">Add Attribute</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="attribute_name" class="form-label">Attribute Name</label>
                            <input type="text" name="attribute_name" id="attributeName" class="form-control"
                                placeholder="e,g, Color, Size" required="">
                            <div class="invalid-feedback">
                                {{ __('Attribute name can not be empty') }}
                            </div>
                        </div>

                        <input type="hidden" name="attribute_id" id="attributeId">

                        <label class="form-label">Attribute Values</label>
                        <div id="valueInputs">
                            <div class="input-group mb-2">
                                <input type="text" name="attribute_values[]" class="form-control"
                                    placeholder="e.g. Red, Large" required="">
                                <button type="button" class="btn btn-success addValue ml-2">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Attribute</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).on('click', '.addValue', function() {
            let inputGroup = `
            <div class="input-group mb-2">
                <input type="text" name="attribute_values[]" class="form-control" placeholder="e.g. Red" required>
                <button type="button" class="btn btn-danger removeValue ml-2">×</button>
            </div>`;
            $('#valueInputs').append(inputGroup);
        });

        $(document).on('click', '.removeValue', function() {
            $(this).closest('.input-group').remove();
        });


        $(document).on('click', '.editAttributeBtn', function() {
            const url = $(this).data('href');
            const attribute = $(this).data('attribute');

            // Set form action and method
            $('#attributeForm').attr('action', url);
            $('#formMethod').val('PUT'); // hidden _method input

            // Set attribute ID and name
            $('#attributeId').val(attribute.id);
            $('#attributeName').val(attribute.name);
            $('#attributeModalLabel').text('Edit Attribute');

            // Clear old inputs
            $('#valueInputs').empty();

            // Add current values
            attribute.values.forEach((val, index) => {
                $('#valueInputs').append(`
            <div class="input-group mb-2">
                <input type="text" name="attribute_values[]" class="form-control" value="${val.value}" required>
                <button type="button" class="btn ${index === 0 ? 'btn-success addValue' : 'btn-danger removeValue'}">
                    ${index === 0 ? '+' : '×'}
                </button>
            </div>
        `);
            });

            // Show modal
            $('#attributeModal').modal('show');
        });
    </script>
@endpush
