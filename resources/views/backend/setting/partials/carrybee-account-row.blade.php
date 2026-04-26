@php
    $row = $row ?? [];
    $idx = $idx ?? 0;
    $carrybeeRowNumber = is_numeric($idx) ? (int) $idx + 1 : null;
@endphp
<div class="card mb-3 carrybee-account-row border">
    <div class="card-body py-3">
        <input type="hidden" name="carrybee_accounts[{{ $idx }}][id]"
            value="{{ old("carrybee_accounts.$idx.id", $row['id'] ?? '') }}">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0 text-muted small text-uppercase">
                @if ($carrybeeRowNumber !== null)
                    {{ __('Account') }} #{{ $carrybeeRowNumber }}
                @else
                    {{ __('New account') }}
                @endif
            </h6>
            <button type="button"
                class="btn btn-sm btn-link text-danger carrybee-remove-row p-0">{{ __('Remove') }}</button>
        </div>
        <div class="row">
            <div class="col-md-12 mb-2">
                <label class="form-label small">{{ __('Account name') }} <span class="text-danger">*</span></label>
                <input type="text" name="carrybee_accounts[{{ $idx }}][account_name]"
                    class="form-control form-control-sm"
                    value="{{ old("carrybee_accounts.$idx.account_name", $row['account_name'] ?? ($row['label'] ?? '')) }}"
                    placeholder="{{ __('e.g. Branch 1, Branch 2') }}" autocomplete="organization">
            </div>
            <div class="col-md-12 mb-2">
                <label class="form-label small">{{ __('API URL') }} <span class="text-danger">*</span></label>
                <input type="url" name="carrybee_accounts[{{ $idx }}][api_url]"
                    class="form-control form-control-sm"
                    value="{{ old("carrybee_accounts.$idx.api_url", $row['api_url'] ?? '') }}"
                    placeholder="https://sandbox.carrybee.com">
            </div>
            <div class="col-md-12 mb-2">
                <label class="form-label small">{{ __('Client ID') }} <span class="text-danger">*</span></label>
                <input type="text" name="carrybee_accounts[{{ $idx }}][client_id]"
                    class="form-control form-control-sm font-monospace"
                    value="{{ old("carrybee_accounts.$idx.client_id", $row['client_id'] ?? '') }}" autocomplete="off">
            </div>
            <div class="col-md-12 mb-2">
                <label class="form-label small">{{ __('Client secret') }} <span class="text-danger">*</span></label>
                <input type="text" name="carrybee_accounts[{{ $idx }}][client_secret]"
                    class="form-control form-control-sm font-monospace carrybee-client-secret-input"
                    value="{{ old("carrybee_accounts.$idx.client_secret", $row['client_secret'] ?? '') }}"
                    autocomplete="off" spellcheck="false">
            </div>
            <div class="col-md-12 mb-2">
                <label class="form-label small">{{ __('Client context') }} <span class="text-danger">*</span></label>
                <input type="text" name="carrybee_accounts[{{ $idx }}][client_context]"
                    class="form-control form-control-sm font-monospace"
                    value="{{ old("carrybee_accounts.$idx.client_context", $row['client_context'] ?? '') }}"
                    autocomplete="off">
            </div>
            <div class="col-md-12">
                <label class="form-label small">{{ __('Carrybee store ID') }}</label>
                <input type="text" name="carrybee_accounts[{{ $idx }}][store_id]"
                    class="form-control form-control-sm font-monospace"
                    value="{{ old("carrybee_accounts.$idx.store_id", $row['store_id'] ?? '') }}"
                    placeholder="{{ __('UUID from GET /api/v2/stores — parcels use this pickup store') }}"
                    autocomplete="off">
                <small
                    class="text-muted">{{ __('Leave blank to use Carrybee default pickup store from the API.') }}</small>
            </div>
        </div>
    </div>
</div>
