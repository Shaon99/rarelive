@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.email.templates') }}">{{ __('Template') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="f-12">{{ __('Variables Meaning') }}</h6>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>{{ __('Variable') }}</th>
                                        <th>{{ __('Meaning') }}</th>
                                    </tr>

                                    @foreach ($template->meaning as $key => $temp)
                                        <tr>
                                            <td>{{ '{' . $key . '}' }}</td>
                                            <td>{{ $temp }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="" method="post" class="needs-validation" novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="subject">{{ __('Subject') }}</label>
                                        <input type="text" name="subject" class="form-control"
                                            value="{{ $template->subject }}">
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label for="template">{{ __('Template') }} <small class="text-danger">* (Do not
                                                change {variable} name)</small></label>
                                        <textarea name="template" id="description" class="form-control ">{{ $template->template }}</textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary float-right"> <i
                                                class="fas fa-save"></i> {{ __('Update Template') }}</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.2.0/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea#description',
            menubar: true,
            branding: false,
            plugins: 'code table lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | table',
        });
    </script>
@endpush
