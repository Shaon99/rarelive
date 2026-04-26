@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.roles.index') }}">{{ __('Role List') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-end mb-3">
                                @if (auth()->guard('admin')->user()->can('permission_add'))
                                <button type="button" class="btn btn-primary btn-sm addPermission">
                                    <i class="fas fa-plus-circle"></i> Add Permission
                                </button>
                                @endif
                            </div>
                            <!--begin::Table-->
                            <table class="table" id="table_1">
                                <thead>
                                    <tr>
                                        <th class="w-10px pe-2">
                                            SL
                                        </th>
                                        <th>Name</th>
                                        <th>Display Name</th>
                                        <th></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(@$permissions->where('submodule_id','!=',0) as $permission)
                                        <tr>
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>
                                            <td>{{ $permission->name }}</td>
                                            <td>{{ $permission->display_name }}</td>
                                            <td></td>
                                            <td class="text-end">
                                                @if (auth()->guard('admin')->user()->can('permission_edit'))
                                                <button type="button" class="btn btn-sm btn-primary mr-2 editPermission"
                                                    data-item="{{ $permission }}"
                                                    data-href="{{ route('admin.permissionUpdate', $permission->id) }}">
                                                    <i class="fa fa-pen"></i>
                                                </button>
                                                @endif
                                                @if (auth()->guard('admin')->user()->can('permission_delete'))
                                                <button data-href="{{ route('admin.destroyPermission', $permission->id) }}"
                                                    class="btn btn-danger btn-sm delete" data-toggle="tooltip"
                                                    title="Delete" type="button">
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
        </section>
        <div class="modal fade" id="addPermission">
            <div class="modal-dialog" role="document">
                <form action="{{ route('admin.permissionPost') }}" method="POST" class="needs-validation" novalidate="">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title title" id="confirmStatusModalLabel">Add Permission</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control name" placeholder="Enter name"
                                        required />
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Display Name</label>
                                    <input type="text" name="display_name" class="form-control display_name"
                                        placeholder="Enter display name" required />
                                    <!--end::Input-->
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Sub Module</label>
                                    <select class="form-control select2 submodule" name="submodule">
                                        <option value="" selected disabled>Select Module</option>
                                        @forelse ($permissions->where('submodule_id','==',0) as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->display_name }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            // Initialize Select2 globally but attach to modal dynamically
            $('.select2').select2({
                dropdownParent: $('#addPermission')
            });

            // Open Modal for Adding Permission
            $(document).on('click', '.addPermission', function(e) {
                const modal = $('#addPermission');
                $('.title').text('Add Permission');
                $('.name').val('');
                $('.display_name').val('');
                $('.submodule').val('').trigger('change');
                modal.modal('show');
            });

            // Open Modal for Editing Permission
            $(document).on('click', '.editPermission', function(e) {
                const modal = $('#addPermission');
                $('.title').text('Edit Permission');
                const item = $(this).data('item');
                $('.name').val(item.name);
                $('.display_name').val(item.display_name);
                $('.submodule').val(item.submodule_id).trigger('change');
                modal.find('form').attr('action', $(this).data('href'));
                modal.modal('show');
            });

            // Reinitialize Select2 when modal is shown
            $('#addPermission').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#addPermission')
                });
            });
        });
    </script>
@endpush
