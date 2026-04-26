<table class="table table-bordered table-responsive">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Initial</th>
            <th>Without Purchase</th>
            <th>Purchased</th>
            <th>Returned</th>
            <th>Sold</th>
            <th>Damaged</th>
            <th>Stock In</th>
            <th>Stock Out</th>
            <th>Current Stock</th>
            <th>AVG Unit Purchases Price</th>
            <th>AVG Stock Price</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @foreach ($products as $index => $product)
            @php
                $stockTransactions = $product->stockTransactions;

                $initial =
                    $stockTransactions->where('type', 'initial')->sortByDesc('created_at')->first()?->quantity ?? 0;

                // Calculate other stock metrics
                $update = $stockTransactions->where('type', 'update')->sum('quantity');
                $damage = $stockTransactions->where('type', 'damage')->sum('quantity');
                $purchased = $product->purchased ?? 0;
                $sold = $product->sold ?? 0;
                $stockIn = $initial + $update + $purchased;
                $stockOut = $sold + $damage;
                $currentStock = $stockIn + $product->sales_returned - $stockOut;
                $avgStockPrice = $currentStock > 0 ? number_format($product->purchase_price, 2) : 0;
            @endphp
            <tr>
                <td>{{ $products->firstItem() + $index }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $initial }}</td>
                <td>{{ $update }}</td>
                <td>{{ $purchased }}</td>
                <td>{{ $product->sales_returned }}</td>
                <td>{{ $sold }}</td>
                <td>{{ $damage }}</td>
                <td>{{ $stockIn }}</td>
                <td>{{ $stockOut }}</td>
                <td><strong>{{ $currentStock }}</strong></td>
                <td>{{ number_format($product->average_unit_price, 2) }} {{ $general->site_currency }}</td>
                <td>{{ $avgStockPrice }} {{ $general->site_currency }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@if ($products->hasPages())
    {{ $products->links('backend.partial.paginate') }}
@endif
