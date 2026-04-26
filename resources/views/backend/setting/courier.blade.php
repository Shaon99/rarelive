<!-- STEADFAST -->
<div class="col-md-4 mb-4">
    <div class="integration-card">
        <div class="d-flex justify-content-between mb-2">
            <div class="integration-logo steadfast-logo">
                <x-heroicon-o-truck class="integration-icon-main" />
            </div>
            <a href="https://steadfast.com.bd/" target="_blank" class="external-link text-center">steadfast.com.bd
                <x-heroicon-o-arrow-top-right-on-square class="hero-icon" /></a>
        </div>
        <h5>STEADFAST</h5>
        <p class="text-muted mb-4">A courier service integration for seamless delivery
            management and tracking of your shipments.</p>
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-light btn-sm d-flex align-items-center justify-content-center" data-toggle="modal"
                data-target="#steadfastModal">
                <x-heroicon-o-cog-8-tooth class="mr-2 hero-icon" />
                <span>Manage</span>
            </button>
            <div class="d-flex align-items-center">
                @if ($setting->enable_online_deliver == 1)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- CARRYBEE -->
<div class="col-md-4 mb-4">
    <div class="integration-card">
        <div class="d-flex justify-content-between mb-2">
            <div class="integration-logo carrybee-logo">
                <x-heroicon-o-truck class="integration-icon-main" />
            </div>
            <a href="https://carrybee.com/" target="_blank" class="external-link text-center">carrybee.com
                <x-heroicon-o-arrow-top-right-on-square class="hero-icon" /></a>
        </div>
        <h5 class="text-uppercase">Carrybee</h5>
        <p class="text-muted mb-4">{{ __('Manage one or more Carrybee accounts in Integrations Settings') }}</p>
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-light btn-sm d-flex align-items-center justify-content-center" data-toggle="modal"
                data-target="#carrybeeModal">
                <x-heroicon-o-cog-8-tooth class="mr-2 hero-icon" />
                <span>Manage</span>
            </button>
            <div class="d-flex align-items-center">
                @if ($carrybeeConfigured && ($setting->enable_carrybee ?? 0) == 1)
                    <span class="badge badge-success">Active</span>
                @elseif ($carrybeeConfigured)
                    <span class="badge badge-warning">Off</span>
                @else
                    <span class="badge badge-secondary">Not configured</span>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- PATHAO -->
