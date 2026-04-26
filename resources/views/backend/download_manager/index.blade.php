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
                        <div class="card-body">
                            <form id="deleteMultipleForm" action="{{ route('admin.download.manager.delete.multiple') }}"
                                method="POST">
                                @csrf
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-danger btn-sm d-none" id="deleteSelected" disabled>
                                        <i class="fas fa-trash mr-1"></i>  Delete Selected
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selectAll" title="Select All">
                                                </th>
                                                <th>SL</th>
                                                <th>File Name</th>
                                                <th>Date</th>
                                                <th class="text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($files as $index => $file)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="files[]" value="{{ $file['name'] }}"
                                                            class="fileCheckbox">
                                                    </td>
                                                    <td>{{ $files->firstItem() + $index }}</td>
                                                    <td>{{ $file['name'] }}</td>
                                                    <td>{{ $file['date'] }}</td>
                                                    <td class="text-right">
                                                        <!-- Download Button -->
                                                        <a href="{{ $file['url'] }}" class="btn btn-success btn-sm mr-2"
                                                            data-toggle="tooltip" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>

                                                        <!-- Delete Button -->
                                                        <button class="btn btn-danger btn-sm deleteforever"
                                                            data-href="{{ route('admin.download.manager.delete', $file['name']) }}"
                                                            data-toggle="tooltip" title="Delete" type="button">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="100%" class="text-center">
                                                        {{ __('No files available') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                            </form>
                            @if ($files->hasPages())
                                {{ $files->links('backend.partial.paginate') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </section>
    </div>
@endsection
