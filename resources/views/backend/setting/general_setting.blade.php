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
                <div class="col-md-12 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <form action="" method="post" enctype="multipart/form-data" class="needs-validation"
                                novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="sitename">{{ __('Organization Name') }}</label>
                                        <input type="text" name="sitename"
                                            placeholder="{{ __('Enter organization name') }}" class="form-control"
                                            value="{{ $general->sitename ?? '' }}">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="site_currency">{{ __('Organization Currency') }}</label>
                                        <input type="text" name="site_currency" class="form-control"
                                            placeholder="{{ __('Enter organization currency') }}"
                                            value="{{ $general->site_currency ?? '৳' }}">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="sitename">{{ __('Website') }}</label>
                                        <input type="text" name="website" placeholder="@lang('enter website Link')"
                                            class="form-control form_control" value="{{ $general->website ?? '' }}">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="phone">{{ __('Organization Phone') }}</label>
                                        <input type="text" name="phone"
                                            placeholder="{{ __('Enter organization phone') }}" class="form-control"
                                            value="{{ $general->site_phone ?? '' }}">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="sitename">{{ __('Invoice header Note') }}</label>
                                        <input type="text" name="invoice_header_note" placeholder="@lang('enter header Invoice Note')"
                                            class="form-control form_control"
                                            value="{{ $general->invoice_header_note ?? '' }}">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="address">{{ __('Organization Address') }}</label>
                                        <input type="text" name="address"
                                            placeholder="{{ __('Enter organization address') }}" class="form-control"
                                            value="{{ $general->site_address ?? '' }}">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="invoice">{{ __('Invoice Greeting') }}</label>
                                        <input type="text" name="invoice_greeting"
                                            placeholder="{{ __('Enter invoice greeting') }}" class="form-control"
                                            value="{{ $general->invoice_greeting ?? '' }}">
                                    </div>

                                    <div class="col-md-3 form-group">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <label for="invoice">{{ __('POS Sound') }}</label>
                                                <div class="toggle-container">
                                                    <label class="switch">
                                                        <input type="checkbox" id="toggle-sound">
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mx-2">
                                                <label for="invoice">{{ __('POS Invoice') }}</label>
                                                <div class="toggle-container">
                                                    <label class="switch">
                                                        <input type="checkbox" id="toggle-sound" name="pos_invoice_on_off"
                                                            {{ $general->pos_invoice_on_off ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="invoice">{{ __('Fraud Check') }}</label>
                                                <div class="toggle-container">
                                                    <label class="switch">
                                                        <input type="checkbox" id="toggle-sound" name="fraud_check_on_off"
                                                            {{ $general->fraud_check_on_off ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <label for="invoice">{{ __('POS Platform') }}</label>
                                                <div class="toggle-container">
                                                    <label class="switch">
                                                        <input type="checkbox" id="toggle-sound" name="pos_platform_on_off"
                                                            {{ $general->pos_platform_on_off ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="invoice">{{ __('POS Lead') }}</label>
                                                <div class="toggle-container">
                                                    <label class="switch">
                                                        <input type="checkbox" id="toggle-sound" name="pos_lead_on_off"
                                                            {{ $general->pos_lead_on_off ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="invoice">
                                                    {{ __('Queue Enabled') }}
                                                    <i class="fas fa-info-circle" data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="{{ __('When enabled, imports and downloads will run in background processes. Requires cron job setup on server.') }}"></i>
                                                </label>
                                                <div class="toggle-container">
                                                    <label class="switch">
                                                        <input type="checkbox" name="queue_enabled"
                                                            {{ env('QUEUE_ENABLED') ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group col-md-3 mb-3">
                                                <label class="col-form-label">{{ __('Organization Logo') }}</label>
                                                <div id="image-preview" class="image-preview"
                                                    style="background-image:url({{ getFile('logo', $general->logo) }});backgroud-repeat : no-repeat;background-size: 100% 100%;">
                                                    <label for="image-upload"
                                                        id="image-label">{{ __('Choose File') }}</label>
                                                    <input type="file" name="logo" id="image-upload" />
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3 mb-3">
                                                <label class="col-form-label">{{ __('Favicon') }}</label>
                                                <div id="image-preview-icon" class="image-preview"
                                                    style="background-image:url({{ getFile('favicon', $general->favicon) }});backgroud-repeat : no-repeat;background-size: 100% 100%;">
                                                    <label for="image-upload-icon"
                                                        id="image-label-icon">{{ __('Choose File') }}</label>
                                                    <input type="file" name="icon" id="image-upload-icon" />
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3 mb-3">
                                                <label class="col-form-label">{{ __('Invoice Logo') }}</label>
                                                <div id="image-preview-invoice" class="image-preview"
                                                    style="background-image:url({{ getFile('logo', $general->invoice_logo) }});backgroud-repeat : no-repeat;background-size: 100% 100%;">
                                                    <label for="image-upload-invoice"
                                                        id="image-label-invoice">{{ __('Choose File') }}</label>
                                                    <input type="file" name="invoice_logo"
                                                        id="image-upload-invoice" />
                                                </div>

                                            </div>

                                            <div class="form-group col-md-3 mb-3">
                                                <label class="col-form-label">{{ __('Default Image') }}</label>
                                                <div id="image-preview-default" class="image-preview"
                                                    style="background-image:url({{ getFile('default', $general->default_image) }});backgroud-repeat : no-repeat;background-size: 100% 100%;">
                                                    <label for="image-upload-default"
                                                        id="image-label-default">{{ __('Choose File') }}</label>
                                                    <input type="file" name="default_image"
                                                        id="image-upload-default" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 mt-5 ">
                                        <button type="submit"
                                            class="btn btn-primary btn-icon icon-left btn-md float-right"><i
                                                class="fas fa-save"></i> {{ __('Save Changes') }}</button>
                                    </div>
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
        $(function() {
            'use strict'
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label",
                label_default: "Choose File",
                label_selected: "Update Image",
                no_label: false,
                success_callback: null
            });

            $.uploadPreview({
                input_field: "#image-upload-icon",
                preview_box: "#image-preview-icon",
                label_field: "#image-label-icon",
                label_default: "Choose File",
                label_selected: "Update Image",
                no_label: false,
                success_callback: null
            });

            $.uploadPreview({
                input_field: "#image-upload-invoice",
                preview_box: "#image-preview-invoice",
                label_field: "#image-label-invoice",
                label_default: "Choose File",
                label_selected: "Update Image",
                no_label: false,
                success_callback: null
            });
            $.uploadPreview({
                input_field: "#image-upload-default",
                preview_box: "#image-preview-default",
                label_field: "#image-label-default",
                label_default: "Choose File",
                label_selected: "Update Image",
                no_label: false,
                success_callback: null
            });
        })
    </script>
@endpush
