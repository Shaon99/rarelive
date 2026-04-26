<div class="position-relative">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>SL</th>
                <th>Invoice No</th>
                <th>Product</th>
                <th>Returned Qty</th>
                <th>Reason</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($returns as $return)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($return->sale)->invoice_no ?? 'N/A' }}</td>
                    <td>{{ optional($return->product)->name ?? 'N/A' }}</td>
                    <td>{{ $return->return_qty }}</td>
                    <td>{{ $return->reason ?? '-' }}</td>
                    <td>{{ $return->created_at?->format('d M Y h:i A') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No returns found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div id="loading-overlay-f" class="loading-overlay custom-loading">
        <div class="loading-overlay-text text-center position-absolute loading-top">please wait...</div>
    </div>
</div>
@if ($returns->hasPages())
    {{ $returns->links('backend.partial.paginate') }}
@endif
