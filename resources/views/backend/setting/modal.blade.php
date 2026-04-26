    <!-- STEADFAST Modal -->
    <div class="modal fade" id="steadfastModal" tabindex="-1" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo steadfast-logo mr-2">
                                <x-heroicon-o-truck class="integration-icon-main" />
                            </div>
                            STEADFAST Integration
                        </div>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.general.integrationSettingUpdate') }}" method="post"
                    class="needs-validation" novalidate="">
                    @csrf
                    <input type="hidden" name="form_type" value="steadfast">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 col-12">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Base Url') }}</label>
                                    <input type="text" name="steadfast_base_url"
                                        placeholder="Enter steadfast base url" class="form-control form_control"
                                        value="{{ config('services.steadfast.base_url') ?? 'https://portal.packzy.com/api/v1' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-md-12 col-12">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('API Key') }}</label>
                                    <input type="text" name="steadfast_api_key" placeholder="Enter steadfast api key"
                                        class="form-control form_control" autocomplete="off"
                                        value="{{ $masked_key }}">
                                </div>
                            </div>

                            <div class="col-md-8 col-12">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('API Secret') }}</label>
                                    <input type="text" name="steadfast_api_secret" class="form-control"
                                        placeholder="Enter steadfast api secret" autocomplete="off"
                                        value="{{ $masked_secret }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('COD Charge') }} (%)</label>
                                    <input type="text" name="steadfast_cod_charge" class="form-control"
                                        placeholder="Enter steadfast cod charge" autocomplete="off"
                                        value="{{ $cod_charge }}">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Webhook URL (Callback URL)') }}</label>
                            <div class="position-relative">
                                <input type="text" readonly class="form-control webhook-url-input" autocomplete="off"
                                    value="{{ url('/steadfast-webhook') }}">
                                <button class="copy-btn" title="Copy to clipboard" type="button">
                                    <span class="icon-default">
                                        <x-heroicon-o-clipboard class="hero-icon" />
                                    </span>
                                    <span class="icon-success d-none">
                                        <x-heroicon-o-clipboard-document-check class="hero-icon text-success" />
                                    </span>
                                </button>
                            </div>
                            <small class="text-muted">Note: Copy this and put it in your Steadfast webhook integration
                                callback url</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Webhook Auth Token(Bearer)</label>
                            <div class="position-relative">
                                <input type="text" class="form-control api-key-input" name="steadfast_webhook_secret"
                                    value="{{ config('services.steadfast.webhook_secret') }}" readonly>
                                <button class="copy-btn" type="button" title="Copy to clipboard">
                                    <span class="icon-default">
                                        <x-heroicon-o-clipboard class="hero-icon" />
                                    </span>
                                    <span class="icon-success d-none">
                                        <x-heroicon-o-clipboard-document-check class="hero-icon text-success" />
                                    </span>
                                </button>
                            </div>
                            <small class="text-muted">Note: Copy this and put it in your Steadfast webhook integration
                                auth
                                token(Bearer)</small>
                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-sm btn-light" type="button">
                                    <x-heroicon-o-arrow-path class="mr-1 hero-icon" /> Regenerate
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Enable Steadfast Courier</label> <br>
                            <div class="d-flex align-items-center">
                                <label class="colorinput mb-0 mr-2">
                                    <input name="enable_online_deliver" type="checkbox" value="1"
                                        class="colorinput-input toggle-status" id="steadfast-on-off"
                                        data-target="steadfast-status"
                                        {{ $setting->enable_online_deliver == 1 ? 'checked' : '' }} />
                                    <span class="colorinput-color bg-primary"></span>
                                </label>
                                <span id="steadfast-status" class="text-muted">
                                    {{ $setting->enable_online_deliver == 1 ? 'ON' : 'OFF' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer mt-0 pt-0">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary save-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CARRYBEE Modal -->
    <div class="modal fade" id="carrybeeModal" tabindex="-1" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo carrybee-logo mr-2">
                                <x-heroicon-o-truck class="integration-icon-main" />
                            </div>
                            Carrybee Integration
                        </div>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.general.integrationSettingUpdate') }}" method="post"
                    class="needs-validation" novalidate="">
                    @csrf
                    <input type="hidden" name="form_type" value="carrybee">
                    <div class="modal-body">
                        @php
                            $__carrybeeFormErrors = false;
                            foreach (array_keys($errors->messages()) as $__k) {
                                if (str_starts_with($__k, 'carrybee_accounts')) {
                                    $__carrybeeFormErrors = true;
                                    break;
                                }
                            }
                        @endphp
                        @if ($__carrybeeFormErrors)
                            <div class="alert alert-danger small">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @php
                            $__carrybeeRows = old('carrybee_accounts');
                            if ($__carrybeeRows === null) {
                                $__carrybeeRows = $carrybeeAccountsForm ?? [];
                            }
                            if (!is_array($__carrybeeRows) || count($__carrybeeRows) === 0) {
                                $__carrybeeRows = [
                                    [
                                        'id' => '',
                                        'account_name' => '',
                                        'label' => '',
                                        'api_url' => '',
                                        'client_id' => '',
                                        'client_secret' => '',
                                        'client_context' => '',
                                        'store_id' => '',
                                    ],
                                ];
                            }
                            $__carrybeeNextIdx = 0;
                            foreach (array_keys($__carrybeeRows) as $__cbKey) {
                                if (is_numeric($__cbKey)) {
                                    $__carrybeeNextIdx = max($__carrybeeNextIdx, (int) $__cbKey + 1);
                                }
                            }
                        @endphp
                        <div id="carrybee-accounts-container" class="row"
                            data-next-index="{{ $__carrybeeNextIdx }}" data-account-label="{{ __('Account') }}">
                            @foreach ($__carrybeeRows as $idx => $row)
                                <div class="col-12 col-md-6 mb-3 carrybee-account-col">
                                    @include('backend.setting.partials.carrybee-account-row', [
                                        'idx' => $idx,
                                        'row' => is_array($row) ? $row : [],
                                    ])
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="carrybee-add-row">
                            <i class="fas fa-plus mr-1"></i>{{ __('Add another account') }}
                        </button>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Carrybee webhook URL') }}</label>
                            <div class="position-relative">
                                <input type="text" readonly class="form-control webhook-url-input"
                                    autocomplete="off" value="{{ url('/carrybee-webhook') }}">
                                <button class="copy-btn" title="{{ __('Copy to clipboard') }}" type="button">
                                    <span class="icon-default">
                                        <x-heroicon-o-clipboard class="hero-icon" />
                                    </span>
                                    <span class="icon-success d-none">
                                        <x-heroicon-o-clipboard-document-check class="hero-icon text-success" />
                                    </span>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ __('Carrybee checker expects HTTP 202 and header') }}
                                <code>X-CB-Webhook-Integration-Header</code>
                                {{ __('— paste the integration UUID from Carrybee below, then Save.') }}
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Webhook integration ID (UUID from Carrybee)') }}</label>
                            <input type="text" class="form-control" name="carrybee_webhook_integration_id"
                                autocomplete="off"
                                value="{{ old('carrybee_webhook_integration_id', $setting->carrybee_webhook_integration_id ?? '') }}"
                                placeholder="40489fe0-9386-4fc9-8e92-2b2fcb9d451c">
                            <small class="text-muted d-block mt-1">
                                {{ __('Must match the value Carrybee sends in') }}
                                <code>X-CB-Webhook-Integration-Header</code>.
                            </small>
                        </div>
                        <div class="mb-3">
                            <label
                                class="form-label">{{ __('Optional: legacy signature (X-Carrybee-Webhook-Signature)') }}</label>
                            <div class="position-relative">
                                <input type="text" readonly class="form-control api-key-input" autocomplete="off"
                                    id="carrybee-webhook-secret-input" name="carrybee_webhook_secret"
                                    value="{{ old('carrybee_webhook_secret', $setting->carrybee_webhook_secret ?? '') }}"
                                    placeholder="{{ __('Click Generate, then Save, or save once to auto-create') }}">
                                <button class="copy-btn" type="button" title="{{ __('Copy to clipboard') }}">
                                    <span class="icon-default">
                                        <x-heroicon-o-clipboard class="hero-icon" />
                                    </span>
                                    <span class="icon-success d-none">
                                        <x-heroicon-o-clipboard-document-check class="hero-icon text-success" />
                                    </span>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ __('Use the same value in Carrybee as the webhook signature / shared secret.') }}
                            </small>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="button" class="btn btn-sm btn-light" id="carrybee-regenerate-webhook">
                                    <x-heroicon-o-arrow-path class="mr-1 hero-icon" /> {{ __('Generate new secret') }}
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label">Enable Carrybee</label> <br>
                            <input type="hidden" name="enable_carrybee" value="0">
                            <div class="d-flex align-items-center">
                                <label class="colorinput mb-0 mr-2">
                                    <input name="enable_carrybee" type="checkbox" value="1"
                                        class="colorinput-input toggle-status" id="carrybee-on-off"
                                        data-target="carrybee-status"
                                        {{ (string) old('enable_carrybee', (string) ($setting->enable_carrybee ?? '0')) === '1' ? 'checked' : '' }} />
                                    <span class="colorinput-color bg-primary"></span>
                                </label>
                                <span id="carrybee-status" class="text-muted">
                                    {{ (string) old('enable_carrybee', (string) ($setting->enable_carrybee ?? '0')) === '1' ? 'ON' : 'OFF' }}
                                </span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ __('You can enable only when at least one account row is saved successfully.') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer mt-0 pt-0">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary save-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
