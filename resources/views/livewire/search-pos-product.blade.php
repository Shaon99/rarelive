<div class="col-md-12 col-lg-12 product-scroll">
    <div class="d-flex justify-content-between mb-3">
        @php
            $authUser = auth()->guard('admin')->user();
            $isAdmin = $authUser->hasRole('Admin'); // Assuming you're using Spatie Role package
        @endphp

        @if ($isAdmin)
            <select class="form-control flex-grow-1 select-box-branch" wire:model.live="warehouse" name="warehouse">
                <option value="" selected disabled>Select Branch</option>
                @foreach ($warehouses as $item)
                    <option value="{{ $item->id }}"
                        {{ ($warehouse ?? null) == $item->id || (!isset($warehouse) && $loop->first) ? 'selected' : '' }}>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        @else
            <select class="form-control flex-grow-1 select-box-branch" name="warehouse" disabled>
                @php
                    $userWarehouse = $warehouses->firstWhere('id', $authUser->warehouse_id);
                @endphp
                @if ($userWarehouse)
                    <option value="{{ $userWarehouse->id }}" selected>{{ $userWarehouse->name }}</option>
                @endif
            </select>

            <!-- Hidden field to send the warehouse ID -->
            <input type="hidden" name="warehouse" value="{{ $userWarehouse->id }}">
        @endif

        <input type="text" wire:model.live.debounce.300ms="search" class="form-control searchInputBox mx-2"
            placeholder="Search by {{ $showComboProducts ? 'combo name' : 'name or code' }}...">
        <button wire:click="toggleComboProducts" type="button" class="btn btn-primary btn-sm">
            {{ $showComboProducts ? 'Products' : 'Combo' }}
        </button>
    </div>
    <!-- Products -->
    <div class="row">
        <div wire:loading.delay class="loading-overlay mt-5" style="display: none">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @if ($showComboProducts)
            @forelse ($combos  as $item)
                <div class="col-6 col-md-3 col-lg-3">
                    <div class="card border-product cursor-pointer mb-2" id="combo-product-img"
                        data-combo-product="{{ $item }}" data-combo-quantity="{{ $item->quantity }}">
                        <img src="{{ getFile('default', $general->default_image) }}" alt="img" class="img-fluid">
                        <center>
                            <p class="mb-0 mt-1 mx-1">{{ $item->name }} ({{ $item->quantity }})</p>
                            <p class="text-primary mb-2 mt-0 font-weight-semibold">
                                {{ number_format($item->price, 2) . ' ' . $general->site_currency }}</p>
                        </center>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <p class="text-center">No products available in inventory</p>
                </div>
            @endforelse
        @else
            @forelse ($products as $item)
                <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                    <div class="card border-product mb-2 cursor-pointer product-card" id="product-img"
                        data-product="{{ $item }}" data-quantity="{{ $item->warehouses->first()->quantity }}">
                        <div class="product-image-wrapper">
                            @if ($item->image)
                                <img src="{{ $item->image }}" alt="img"
                                    class="img-fluid product-image">
                            @else
                                <img src="{{ getFile('default', $general->default_image) }}" alt="img"
                                    class="img-fluid product-image">
                            @endif
                        </div>

                        <div class="product-details text-center px-2 py-1">
                            <p class="mb-1 text-truncate" data-toggle="tooltip" title="{{ $item->name }}">
                                {{ $item->name }}</p>
                            <small class="text-muted d-block text-truncate" title="{{ $item->code }}">
                                {{ $item->code }} (Qty: {{ $item->warehouses->first()->quantity ?? 0 }})
                            </small>
                            <p class="text-primary mb-1 font-weight-semibold">
                                {{ number_format($item->sale_price, 2) . ' ' . $general->site_currency }}
                            </p>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-md-12">
                    <p class="text-center">No products available in inventory</p>
                </div>
            @endforelse
    </div>
    <!-- Load More Button -->
    @if ($products->hasMorePages())
        <div class="col-12 text-center mb-2">
            <button wire:click="loadMoreProducts" type="button" class="btn btn-primary mt-3">
                Load More Products
            </button>
        </div>
    @endif
    @endif
</div>
