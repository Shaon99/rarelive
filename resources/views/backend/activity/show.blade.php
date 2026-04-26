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
                            <p class="card-title mb-2 border-bottom">Activity Information</p>
                            <ul class="list-unstyled">
                                <li><strong>Description:</strong> <span>{{ $activity->description }}</span></li>
                                <li><strong>Caused By:</strong>
                                    <span>{{ $activity->causer ? $activity->causer->name : 'N/A' }}</span>
                                </li>
                                <li><strong>Created At:</strong>
                                    <span>{{ $activity->created_at->format('d M, y H:i:s A') }}</span></li>
                            </ul>

                            <div class="row">
                                @if ($activity->subject)
                                    <div class="col-md-6">
                                        <p class="card-title mb-2 border-bottom">Perform On Details</p>
                                        <div class="card">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Key</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach (json_decode(json_encode($activity->subject), true) as $key => $value)
                                                            <tr>
                                                                <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>
                                                                </td>
                                                                <td>
                                                                    @if (is_array($value))
                                                                        <pre class="bg-light p-2 rounded">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                    @else
                                                                        <span class="text-muted">{{ $value }}</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                @endif
                            </div>
                            <div class="col-md-6 px-0">
                                <p class="card-title mb-2 border-bottom">Activity Properties</p>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Property</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->properties as $key => $value)
                                                <tr>
                                                    <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>
                                                    </td>
                                                    <td>
                                                        @if (is_array($value))
                                                            <div class="bg-light p-2 rounded">
                                                                <ul class="list-unstyled mb-0">
                                                                    @foreach ($value as $subKey => $subValue)
                                                                        <li>
                                                                            <strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong>
                                                                            <span class="text-muted">
                                                                                @if (is_array($subValue))
                                                                                    {{ json_encode($subValue, JSON_PRETTY_PRINT) }}
                                                                                @else
                                                                                    {{ $subValue }}
                                                                                @endif
                                                                            </span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">{{ $value }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </section>
    </div>
@endsection
