@extends('backend.layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.customer.index') }}">{{ __('Customers') }}</a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.customer.update', $singleCustomers->id) }}" method="POST"
                                class="needs-validation" novalidate="">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $singleCustomers->name) }}"
                                            placeholder="Enter customer name" required="">
                                        <div class="invalid-feedback">
                                            {{ __('name can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Phone') }}</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone', $singleCustomers->phone) }}"
                                            placeholder="Enter customer phone" required="">
                                        <div class="invalid-feedback">
                                            {{ __('phone can not be empty') }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-12">
                                        <label>{{ __('Email') }}</label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email', $singleCustomers->email) }}"
                                            placeholder="Enter customer email">
                                    </div>

                                    <div class="form-group col-md-4 col-4">
                                        <label>Social Platform / Order From</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="social_type"
                                                    id="facebookRadio" value="facebook" @checked(old('social_type', $singleCustomers->social_type) == 'facebook')>
                                                <label class="form-check-label" for="facebookRadio">Facebook</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="social_type"
                                                    id="whatsappRadio" value="whatsapp" @checked(old('social_type', $singleCustomers->social_type) == 'whatsapp')>
                                                <label class="form-check-label" for="whatsappRadio">WhatsApp</label>
                                            </div>
                                        </div>

                                        {{-- Social ID Input --}}
                                        @php
                                            $socialType = old('social_type', $singleCustomers->social_type);
                                            $socialId = old('social_id', $singleCustomers->social_id);
                                        @endphp
                                        <div id="socialIdInput" class="mt-2 {{ $socialType ? '' : 'd-none' }}">
                                            <label id="socialIdLabel">
                                                {{ $socialType === 'whatsapp' ? 'WhatsApp Number' : 'Facebook ID' }}
                                            </label>
                                            <input type="{{ $socialType === 'whatsapp' ? 'number' : 'text' }}"
                                                name="social_id" id="social_id" class="form-control"
                                                placeholder="{{ $socialType === 'whatsapp' ? 'Enter WhatsApp Number' : 'Enter Facebook ID' }}"
                                                value="{{ $socialId }}">
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
                                        <select class="form-control select2" name="thana" id="thanaSelect" disabled>
                                            <option value="" selected disabled>{{ __('Select Thana') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-12 col-12">
                                        <label>{{ __('Address (max 400 char)' ) }}</label>
                                        <textarea name="address" id="newAddress" maxlength="400" rows="2" value="{{ old('address') }}"
                                            placeholder="Enter Road, House, village" class="form-control"
                                            title="Don't have a existing address or add another address then add address"></textarea>

                                        <ul class="address-li mt-2">
                                            <div class="font-weight-bold">
                                                Existing address:
                                            </div>
                                            @forelse ($singleCustomers->addressBooks as $item)
                                                <li class="mb-2">
                                                    {{ $loop->iteration }}.
                                                    {{ $item->address }},
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary btn-sm mx-2 editAddress"
                                                        data-toggle="modal" data-target="#editAddressModal"
                                                        data-id="{{ $item->id }}"
                                                        data-href="{{ route('admin.address.edit', $item->id) }}"
                                                        data-city="{{ $item->city }}" data-thana="{{ $item->thana }}"
                                                        data-address="{{ $item->address }}">
                                                        <i class="fas fa-pen"></i>
                                                    </button>

                                                    <button class="btn btn-danger btn-sm delete"
                                                        data-href="{{ route('admin.address.destroy', $item->id) }}"
                                                        data-toggle="tooltip" title="Delete" type="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </li>
                                            @empty
                                            @endforelse
                                        </ul>

                                        <div class="invalid-feedback">
                                            {{ __('address can not be empty') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary" type="submit">{{ __('Update Customer') }}</button>
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
    <script type="text/javascript" src="{{ asset('assets/admin/js/customer_edit.js') }}"></script>
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
