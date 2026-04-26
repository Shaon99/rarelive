@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.roles.create') }}"> {{ __('Create Role') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-4 text-capitalize">{{ $role->name }} Has
                                Permissions</h6>
                            <div class="table-responsive">
                                <table class="table table-row-dashed">
                                    <tbody >
                                        @forelse($parentSelectedPermissions as $parentSelectedPermission)
                                            <tr>
                                                <td>
                                                    {{ $parentSelectedPermission->display_name }}
                                                </td>

                                                <td>
                                                    <div class="row py-2">
                                                        @foreach ($permissions as $permission)
                                                            @if ($parentSelectedPermission->id == $permission->submodule_id)
                                                                <div class="col-md-3 col-lg-3 col-sm-2 col-12">
                                                                    <label
                                                                        class="form-check form-check-sm form-check-custom form-check-solid">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            checked disabled />
                                                                        <span class="form-check-label">
                                                                            {{ $permission->display_name }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
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
