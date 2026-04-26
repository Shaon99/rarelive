<table class="table table-bordered">
    <thead>
        <tr>
            <th>SL</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Reference no') }}</th>
            <th>{{ __('Invoice no') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Warehouse') }}</th>
            <th>{{ __('Supplier') }}</th>
            <th>{{ __('Grand Total') }}</th>
            <th>{{ __('Paid Amount') }}</th>
            <th>{{ __('Due Amount') }}</th>
            <th>{{ __('Payment Status') }}</th>
            <th>{{ __('created At') }}</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse ($purchases as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    @if ($item->order_status == 0)
                        <span class="badge badge-success">{{ __('Recieved') }}</span>
                    @elseif($item->order_status == 1)
                        <span class="badge badge-warning">{{ __('Pending') }}</span>
                    @elseif($item->order_status == 2)
                        <span class="badge badge-primary">{{ __('Ordered') }}</span>
                    @endif
                </td>
                <td>{{ $item->reference_no }}</td>
                <td>
                    <a href="{{ route('admin.purchases.show', $item->id) }}">{{ $item->invoice_no }}</a>
                </td>
                <td>{{ $item->purchase_date }}</td>
                <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                <td>{{ $item->supplier->name ?? 'N/A' }}</td>
                <td>{{ number_format($item->grand_total, 2) . ' ' . $general->site_currency }}
                </td>
                <td>{{ number_format($item->paid_amount, 2) . ' ' . $general->site_currency }}
                </td>
                <td>{{ number_format($item->due_amount, 2) . ' ' . $general->site_currency }}
                </td>
                <td>
                    @if ($item->payment_status == 0)
                        <span class="badge badge-danger">{{ __('Due') }}</span>
                    @elseif($item->payment_status == 1)
                        <span class="badge badge-success">{{ __('Completed') }}</span>
                    @endif
                </td>
                <td>{{ $item->created_at->format('d M, Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">no record found</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="1"><strong>{{ __('Total') }}: {{ $purchases->count() }}</strong></td>
            <td colspan="6" class="text-right"><strong>{{ __('Total') }}:</strong></td>
            <td><strong>{{ number_format($purchases->sum('grand_total'), 2) . ' ' . $general->site_currency }}</strong></td>
            <td><strong>{{ number_format($purchases->sum('paid_amount'), 2) . ' ' . $general->site_currency }}</strong></td>
            <td><strong>{{ number_format($purchases->sum('due_amount'), 2) . ' ' . $general->site_currency }}</strong></td>
        </tr>
    </tfoot>
</table>
@if ($purchases->hasPages())
    {{ $purchases->links('backend.partial.paginate') }}
@endif
