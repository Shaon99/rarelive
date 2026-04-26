<!-- CLOUDINARY -->
<div class="col-md-4 mb-4">
    <div class="integration-card">
        <div class="d-flex justify-content-between mb-2">
            <div class="integration-logo cloudinary-logo">
                <x-heroicon-o-cloud-arrow-up class="integration-icon-main" />
            </div>
            <a href="https://cloudinary.com/" target="_blank" class="external-link text-center">cloudinary.com
                <x-heroicon-o-arrow-top-right-on-square class="hero-icon" /></a>
        </div>
        <h5 class="text-uppercase">cloudinary</h5>
        <p class="text-muted mb-4">Image and Video APIs for Fast, Scalable Media
            ManagementStore, transform, optimize,
            and deliver media with powerful APIs</p>
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-light btn-sm d-flex align-items-center justify-content-center" data-toggle="modal"
                data-target="#cloudinaryModal">
                <x-heroicon-o-cog-8-tooth class="mr-2 hero-icon" />
                <span>Manage</span>
            </button>
            <div class="d-flex align-items-center">
                @if ($setting->cloudinary_enable == 1)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- MIM BULK SMS -->
<div class="col-md-4 mb-4">
    <div class="integration-card">
        <div class="d-flex justify-content-between mb-2">
            <div class="integration-logo sms-logo">
                <x-heroicon-o-envelope class="integration-icon-main" />
            </div>
        </div>
        <h5 class="text-uppercase">SMS Service</h5>
        <p class="text-muted mb-4">SMS notification service for seamless customer communication. 
            Keep customers informed about order status, transactions, 
            and important updates through automated SMS alerts</p>
        <div class="d-flex justify-content-between align-items-center">
            <button disabled class="btn btn-light btn-sm d-flex align-items-center justify-content-center" data-toggle="modal"
                data-target="#cloudinaryModal">
                <x-heroicon-o-cog-8-tooth class="mr-2 hero-icon" />
                <span>Manage</span>
            </button>
            <div class="d-flex align-items-center">
                {{-- @if ($setting->cloudinary_enable == 1)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif --}}
                <span class="badge badge-primary">Coming Soon</span>
            </div>
        </div>
    </div>
</div>
