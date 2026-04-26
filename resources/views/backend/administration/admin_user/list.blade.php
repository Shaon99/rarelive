@extends('backend.layout.master')

@section('content')

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.create') }}">{{ __('Create Admin User') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @if (auth()->guard('admin')->user()->can('admin_user_add'))
                            <a href="{{ route('admin.create') }}" class="btn btn-primary"><i class="fas fa-plus-circle"></i> {{ __('Create Admin User') }}</a>
                        @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table_1">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Sl') }}</th>
                                            <th>{{ __('Avatar') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Phone') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Branch') }}</th>
                                            <th>{{ __('Role') }}</th>
                                            @if (auth()->guard('admin')->user()->canany(['admin_user_edit', 'admin_user_delete']))
                                                <th>{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(@$admins as $admin)
                                            <tr>
                                                <td>{{ @$loop->iteration }}</td>
                                                <td>
                                                    @if ($admin->image)
                                                        <img src="{{ getFile('admin', @$admin->image) }}" alt="Image" width="40px"
                                                            class="rounded img-fluid">
                                                    @else
                                                        <img src="{{ getFile('default', @$general->default_image) }}"
                                                            alt="Image" width="40px" class="rounded img-fluid">
                                                    @endif

                                                </td>
                                                <td>{{ $admin->name ?? $admin->username }}</td>
                                                <td>{{ $admin->phone }}</td>
                                                <td>{{ $admin->email }}</td>
                                                <td>{{ $admin->warehouse->name }}</td>
                                                <td>{{ @$admin->getRoleNames()[0] }}</td>
                                                @if (auth()->guard('admin')->user()->canany(['admin_user_edit', 'admin_user_delete']))
                                                    <td>
                                                        <div class="d-flex">
                                                            @if (auth()->guard('admin')->user()->can('admin_user_edit'))
                                                                <a class="btn btn-primary btn-sm mr-1"
                                                                    href="{{ route('admin.edit', @$admin->id) }}"
                                                                    data-toggle="tooltip" title="Edit">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </a>
                                                            @endif
                                                            @if (auth()->guard('admin')->user()->can('admin_user_delete'))
                                                                <button class="btn btn-danger btn-sm delete"
                                                                    data-href="{{ route('admin.destroy', $admin->id) }}"
                                                                    data-toggle="tooltip" title="Delete" type="button">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
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

