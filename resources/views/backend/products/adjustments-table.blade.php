<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>SL</th>
            <th>Product</th>
            <th>Warehouse</th>
            <th>Adjustment Type</th>
            <th>Quantity</th>
            <th>Purchase Price</th>
            <th>Sale Price</th>
            <th>Reason</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse ($adjustments as $adjustment)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $adjustment->product->name }} ({{ $adjustment->product->code }})
                </td>
                <td>{{ $adjustment->warehouse->name }}</td>
                <td>
                    <span
                        class="badge {{ $adjustment->type == 'initial' ? 'badge-success' : ($adjustment->type == 'damage' ? 'badge-danger' : 'badge-warning') }}">
                        {{ ucfirst($adjustment->type) }}
                    </span>
                </td>
                <td>{{ $adjustment->quantity }}</td>
                <td>{{ $adjustment->unit_price ?? 0 }} {{ $general->site_currency }}</td>
                <td>{{ $adjustment->sale_price ?? 0 }} {{ $general->site_currency }}</td>
                <td>{{ $adjustment->reason ?? 'N/A' }}</td>
                <td>{{ $adjustment->created_at->format('Y-m-d H:i A') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">{{ __('No adjustment available') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($adjustments->hasPages())
    {{ $adjustments->links('backend.partial.paginate') }}
@endif
