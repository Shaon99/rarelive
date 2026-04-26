@extends('backend.layout.master')

@section('content')

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __($pageTitle) }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                <div class="breadcrumb-item active"><a
                        href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                </div>
            </div>
        </div>

    <div class="row">

        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-body">

                    <form action="" method="post" enctype="multipart/form-data">

                        @csrf

                        <div class="row">
                            <div class="form-group col-md-6">

                                <label for="">{{__('Email Method')}}</label>
                                <select name="email_method" id="" class="form-control selectric">

                                    <option value="php" {{ $email->email_method == 'php' ? 'selected' : '' }}>{{__('PHPMail')}}</option>
                                    <option value="smtp" {{ $email->email_method == 'smtp' ? 'selected' : '' }}>
                                        {{__('SMTP Mail')}}</option>

                                </select>

                            </div>

                            <div class="form-group col-md-6">

                                <label for="">{{__('Email Sent From')}}</label>

                                <input type="email" name="site_email" class="form-control form_control"  value="{{$email->site_email}}">

                            </div>

                            <div class="form-group col-md-12 smtp-config">


                                @if ($email->email_method == 'smtp')

                                    <div class="row">

                                        <div class="col-md-3">
                                            <label for="">{{__('SMTP HOST')}}</label>
                                            <input type="text" name="email_config[smtp_host]"
                                                 class="form-control"
                                                value="{{ $email->email_config->smtp_host ??""}}">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="">{{__('SMTP Username')}}</label>
                                            <input type="text" name="email_config[smtp_username]"
                                                 class="form-control"
                                                value="{{ $masked_userName??'' }}">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="">{{__('SMTP Password')}}</label>
                                            <input type="password" name="email_config[smtp_password]"
                                                 class="form-control"
                                                value="{{ $masked_password??'' }}">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="">{{__('SMTP port')}}</label>
                                            <input type="text" name="email_config[smtp_port]"
                                                 class="form-control"
                                                value="{{ $email->email_config->smtp_port??'' }}">

                                        </div>

                                        <div class="col-md-6 mt-3">

                                            <label for="">{{__('SMTP Encryption')}}</label>
                                            <select name="email_config[smtp_encryption]" id="encryption" class="form-control selectric">
                                                <option value="ssl"
                                                    {{ $email->email_config->smtp_encryption == 'ssl' ? 'selected' : '' }}>
                                                    {{__('SSL')}}</option>
                                                <option value="tls"
                                                    {{ $email->email_config->smtp_encryption == 'tls' ? 'selected' : '' }}>
                                                    {{__('TLS')}}</option>
                                            </select>

                                            <code class="hint"></code>

                                        </div>

                                    </div>


                                @endif

                            </div>

                            <div class="form-group col-md-12">

                                <button type="submit" class="btn btn-primary float-right"><i class="fas fa-save"></i> {{__('Save Email Configuration')}}</button>

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

            $('select[name=email_method]').on('change', function() {
                if ($(this).val() == 'smtp') {
                    var html = `
                
                     <div class="row">

                                    <div class="col-md-3">

                                        <label for="">{{__('SMTP HOST')}}</label>
                                        <input type="text" name="email_config[smtp_host]"  class="form-control" value="{{ @$email->email_config->smtp_host }}">

                                    </div> 
                                    
                                    <div class="col-md-3">

                                        <label for="">{{__('SMTP Username')}}</label>
                                        <input type="text" name="email_config[smtp_username]"  class="form-control" value="{{ @$email->email_config->smtp_username }}">

                                    </div>
                                    
                                    <div class="col-md-3">

                                        <label for="">{{__('SMTP Password')}}</label>
                                        <input type="text" name="email_config[smtp_password]"  class="form-control" value="{{ @$email->email_config->smtp_password }}">

                                    </div>
                                    <div class="col-md-3">

                                        <label for="">{{__('SMTP port')}}</label>
                                        <input type="text" name="email_config[smtp_port]"  class="form-control" value="{{ @$email->email_config->smtp_port }}">

                                    </div> 
                                    
                                    <div class="col-md-6 mt-3">

                                        <label for="">{{__('SMTP Encryption')}}</label>
                                       <select name="email_config[smtp_encryption]" id="encryption" class="form-control selectric">
                                        <option value="tls" {{ @$email->email_config->smtp_encription == 'tls' ? 'selected' : '' }}>{{__('TLS')}}</option>
                                        <option value="ssl" {{ @$email->email_config->smtp_encription == 'ssl' ? 'selected' : '' }}>{{__('SSL')}}</option>
                                       </select>


                                       <code class="hint"></code>

                                    </div>
                                
                                </div>
                
                `;

                    $('.smtp-config').html(html)

                } else {
                    $('.smtp-config').html('')
                }
            })

            $(document).on('change','#encryption',function(){
               
                if($(this).val() == 'ssl'){
                    $('.hint').text("For SSL please add ssl:// before host otherwise it won't work")
                }else{
                    $('.hint').text('')
                }
            })
        })
    </script>

@endpush
