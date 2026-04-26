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

                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>{{ __('Sl') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Subject') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>

                                    @forelse ($emailTemplates as $key => $email)
                                        <tr>

                                            <td>{{ $key + $emailTemplates->firstItem() }}</td>
                                            <td>{{ str_replace('_', ' ', $email->name) }}</td>
                                            <td>{{ $email->subject }}</td>
                                            <td>
                                                @if (auth()->guard('admin')->user()->can('email_template_edit'))
                                                    <a href="{{ route('admin.email.templates.edit', $email) }}"
                                                        class="btn btn-primary btn-sm btn-icon"><i class="fa fa-pen"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" colspan="100%">{{ __('No Email Template Found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </table>
                            </div>
                        </div>
                        @if ($emailTemplates->hasPages())
                            <div class="card-footer">

                                {{ $emailTemplates->links('backend.partial.paginate') }}

                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endsection
