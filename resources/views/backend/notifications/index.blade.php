@extends('backend.layout.master')
@push('style')
    <style>
        .card-header {
            width: auto !important;
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
                            @include('backend.partial.admin-prune-logs-hint')
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Message') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($notifications as $notification)
                                            <tr>
                                                <td>{{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}
                                                </td>
                                                <td>{{ $notification->data['message'] }}</td>
                                                <td>{{ $notification->created_at->format('d M, y h:i:s A') }}</td>
                                                <td>
                                                    @if (!$notification->read_at)
                                                        <a href="{{ route('admin.notifications.markAsRead', $notification->id) }}"
                                                            class="btn btn-sm btn-primary">
                                                            {{ __('Mark as Read') }}
                                                        </a>
                                                    @else
                                                        <span class="badge badge-success">{{ __('Read') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%" class="text-center">
                                                    {{ __('No notifications available') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($notifications->hasPages())
                            <div class="card-footer">
                                {{ $notifications->links('backend.partial.paginate') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
