@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.product.index') }}">{{ __('Products') }}</a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.customer.store') }}" method="POST" class="needs-validation"
                                novalidate="">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}" placeholder="Enter customer name" required="">
                                        <div class="invalid-feedback">
                                            {{ __('name can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Phone') }}</label>
                                        <input type="number" class="form-control" min="11" name="phone"
                                            value="{{ old('phone') }}" placeholder="Enter customer phone" required="">
                                        <div class="invalid-feedback">
                                            {{ __('phone can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Email') }}</label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email') }}" placeholder="Enter customer email">
                                    </div>

                                    <div class="form-group col-md-4 col-4">
                                        <label>Social Platform / Order From</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="social_type"
                                                    id="facebookRadio" value="facebook"
                                                    @if (old('social_type') == 'facebook') checked @endif>
                                                <label class="form-check-label" for="facebookRadio">Facebook</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="social_type"
                                                    id="whatsappRadio" value="whatsapp"
                                                    @if (old('social_type') == 'whatsapp') checked @endif>
                                                <label class="form-check-label" for="whatsappRadio">WhatsApp</label>
                                            </div>
                                        </div>

                                        {{-- Social ID Input (hidden by default) --}}
                                        <div id="socialIdInput" class="mt-2 {{ old('social_id') ? '' : 'd-none' }}">
                                            <label
                                                id="socialIdLabel">{{ old('social_type') == 'whatsapp' ? 'WhatsApp Number' : 'Facebook ID' }}</label>
                                            <input type="text" name="social_id" id="social_id" class="form-control"
                                                placeholder="{{ old('social_type') == 'whatsapp' ? 'Enter WhatsApp Number' : 'Enter Facebook ID' }}"
                                                value="{{ old('social_id') }}">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('City') }}</label>
                                        <select class="form-control select2" name="city" id="citySelect">
                                            <option value="" selected disabled>{{ __('Select City') }}</option>
                                            @forelse ($districts as $item)
                                                <option value="{{ $item['name'] }}">
                                                    {{ $item['name'] }}
                                                    {{ isset($item['bn_name']) && $item['bn_name'] ? '(' . $item['bn_name'] . ')' : '' }}
                                                </option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Thana') }}</label>
                                        <select class="form-control select2" name="thana" id="thanaSelect"
                                            @if (old('thana')) @else disabled @endif>
                                            <option value="" selected disabled>{{ __('Select Thana') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('Address (max 400 char)') }}</label>
                                        <textarea name="address" id="newAddress" rows="2" maxlength="400" class="form-control" placeholder="Enter Road, House, village" required>{{ old('address') }}</textarea>

                                        <div class="invalid-feedback">
                                            {{ __('Address can not be empty') }}
                                        </div>
                                    </div>

                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary" type="submit">{{ __('Create Customer') }}</button>
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
    <script type="text/javascript" src="{{ asset('assets/admin/js/customer_create.js') }}"></script>
    <script>
        let lastThanaValue1 = null;
        let lastThanaValue2 = null;
        $('#thanaSelect').on('change', function() {
            const currentValue = $(this).val();

            if (!currentValue || currentValue === lastThanaValue1) return;

            lastThanaValue1 = currentValue;

            const cityName = $('#citySelect option:selected').text().trim().replace(/\s+/g, ' ');
            const thanaName = $('#thanaSelect option:selected').text().trim().replace(/\s+/g, ' ');
            const formattedAddress = `City: ${cityName},\nThana: ${thanaName},`;

            $('#newAddress').val(formattedAddress);
        });
    </script>
@endpush
