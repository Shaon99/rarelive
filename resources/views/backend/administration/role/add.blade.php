@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.roles.index') }}">
                            {{ __('Role List') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form class="needs-validation" novalidate="" action="{{ route('admin.roles.store') }}"
                                method="POST">
                                @csrf
                                <div class="d-flex justify-content-center">
                                    <div class="form-group col-md-6">
                                        <label>Role Name</label>
                                        <input class="form-control" placeholder="Enter a role name" name="name"
                                            value="{{ old('name') }}" required="" />
                                    </div>
                                </div>

                                <div class="fv-row">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="text-capitalize mb-0">
                                            Permissions</label>

                                        <div class="search-box" style="width: 300px;">
                                            <input type="text" class="form-control" id="permissionSearch"
                                                placeholder="Search permissions..." onkeyup="filterPermissions()">
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <!--begin::Table-->
                                        <table class="table table-row-dashed">
                                            <!--begin::Table body-->
                                            <tbody>
                                                <!--begin::Table row-->
                                                <tr>
                                                    <td>
                                                        Administrator Access
                                                    </td>
                                                    <td>
                                                        <label class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="roles_select_all" />
                                                            <span class="form-check-label">
                                                                Select all
                                                            </span>
                                                        </label>
                                                    </td>
                                                </tr>
                                                @forelse ($permissions as $permission)
                                                    @php
                                                        $permission_module = Spatie\Permission\Models\Permission::where(
                                                            'submodule_id',
                                                            $permission->id,
                                                        )->get();
                                                    @endphp
                                                    <tr class="permission-row">
                                                        <td>
                                                            {{ $permission->display_name }}</td>
                                                        <td>
                                                            <div class="row py-2">
                                                                @forelse ($permission_module as $item)
                                                                    <div class="col-md-3 col-lg-3 col-sm-2 col-12 permission-item">
                                                                        <!--begin::Checkbox-->
                                                                        <label class="form-check">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                value="{{ $item->id }}"
                                                                                name="group_a[]" />
                                                                            <span class="text-center permission-name">
                                                                                {{ $item->display_name }}
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                @empty
                                                                @endforelse
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center" colspan="100%">
                                                            {{ __('no record found') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="text-center py-3">
                                    <button type="submit" class="btn btn-primary w-50" id="submitBtn">
                                        Create Admin User
                                    </button>
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
        document.addEventListener("DOMContentLoaded", function() {
            const selectAllCheckbox = document.getElementById("roles_select_all");
            const permissionCheckboxes = document.querySelectorAll("input[name='group_a[]']");

            selectAllCheckbox.addEventListener("change", function() {
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });

            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else if (Array.from(permissionCheckboxes).every(cb => cb.checked)) {
                        selectAllCheckbox.checked = true;
                    }
                });
            });
        });

        function filterPermissions() {
            const searchText = document.getElementById('permissionSearch').value.toLowerCase();
            const rows = document.getElementsByClassName('permission-row');

            Array.from(rows).forEach(row => {
                const permissionItems = row.getElementsByClassName('permission-name');
                const mainPermission = row.getElementsByTagName('td')[0].textContent.toLowerCase();
                let shouldShow = false;

                if (mainPermission.includes(searchText)) {
                    shouldShow = true;
                } else {
                    Array.from(permissionItems).forEach(item => {
                        if (item.textContent.toLowerCase().includes(searchText)) {
                            shouldShow = true;
                        }
                    });
                }

                row.style.display = shouldShow ? '' : 'none';
            });
        }
    </script>
@endpush
