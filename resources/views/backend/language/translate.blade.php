@extends('backend.layout.master')
@push('style')
    <style>
        .selectric {
            background-color: #fdfdff;
            border-color: #dadeff;
            min-height: 39px!important;
            border-radius: 3px!important;
            padding-left: 0px;
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
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label class="input-group-text bg-primary text-white" for="inputGroupSelect02">
                                        {{ __('Import From') }}
                                    </label>
                                </div>
                                <select class="custom-select import selectric" id="inputGroupSelect02">
                                    <option selected disabled>{{ __('Select Language') }}</option>
                                    @foreach ($languages as $la)
                                        <option value="{{ $la->short_code }}">{{ __($la->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <form action="" method="post">
                                @csrf
                                <div class="text-right mb-3">
                                    <button type="button" class="btn btn-primary btn-icon btn-sm add-more">
                                        <i class="fa fa-plus-circle"></i> {{ __('Add More') }}
                                    </button>
                                    <button type="submit"
                                        class="btn btn-primary btn-sm">{{ __('Update Language') }}</button>
                                </div>
                                <div class="table-responsive">
                                    <input type="text" id="searchInput" class="form-control mb-3 w-25"
                                        placeholder="Search English Text..." />
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('EN') }}</th>
                                                <th>{{ __($pageTitle) }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append">
                                            @foreach ($translators as $key => $translate)
                                                <tr>
                                                    <td>
                                                        <input type="text" name="key[]" value="{{ $key }}"
                                                            class="form-control" />
                                                    </td>
                                                    <td>
                                                        <input type="text" name="value[]" value="{{ $translate }}"
                                                            class="form-control" />
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Bootstrap Confirmation Modal -->
    <div class="modal fade" id="confirmImportModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">{{ __('Confirm Import') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="importConfirmText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmImportBtn">{{ __('Yes, Import') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        $(function() {
            let i = {{ $translators != null ? count($translators) : 0 }};
            $('.add-more').on('click', function() {
                let html = `
                <tr>
                    <td>
                        <input type="text" name="key[]" class="form-control" />
                    </td>
                    <td>
                        <input type="text" name="value[]" class="form-control" />
                    </td>
                </tr>
            `;
                i++;
                $('#append').prepend(html);
            });

            $(document).ready(function() {
                let selectedLang = "";
                $('.import').on('change', function() {
                    selectedLang = $(this).val();
                    let text = "Are you sure you want to import from " + selectedLang + "?";
                    $('#importConfirmText').text(text);
                    $('#confirmImportModal').modal('show');
                });

                $('#confirmImportBtn').on('click', function() {
                    let current = "{{ request()->lang }}";
                    $.ajax({
                        url: "{{ route('admin.language.import') }}",
                        method: "GET",
                        data: {
                            import: selectedLang,
                            current: current
                        },
                        success: function(response) {
                            showToast(response.message, 'success');
                            window.location.reload(true);
                        }
                    });

                    $('#confirmImportModal').modal('hide');
                });
            });

            document.getElementById("searchInput").addEventListener("keyup", function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll("#append tr");

                rows.forEach(row => {
                    let firstCol = row.querySelector("td input[name='key[]']").value.toLowerCase();
                    if (firstCol.includes(filter)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });
    </script>
@endpush
