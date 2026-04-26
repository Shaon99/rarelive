@extends('backend.layout.master')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col">
                                    <h4>Integrations & Workflows</h4>
                                    <p class="text-muted">Supercharge your workflow and connect the tools you and your team
                                        uses every day.</p>
                                </div>
                            </div>
                            <!-- Integration Cards -->
                            <div class="row mb-4">
                                @include('backend.setting.courier')
                                {{-- @include('backend.setting.others') --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @include('backend.setting.modal')
@endsection
@push('script')
    <script
        src="{{ asset('assets/admin/js/integration.js') }}?v={{ filemtime(public_path('assets/admin/js/integration.js')) }}">
    </script>
@endpush
