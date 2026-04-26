<table class="table">
    <thead>
        <tr>
            <th>#SL</th>
            <th>Invoice No</th>
            <th>Status</th>
            <th>Customer</th>
            <th>Grand Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($recentSales as $item)
            <tr class="border-b">
                <td>{{$loop->iteration}}</td>
                <td><a href="{{ route('admin.invoice', $item->id) }}" target="_blank"> {{ $item->invoice_no }} </a></td>
                <td>
                    @if ($item->system_status === 'pending')
                        <span class="badge badge-warning">{{ __('Shipping') }}</span>
                    @elseif($item->system_status === 'completed')
                        <span class="badge badge-success">{{ __('Delivered') }}</span>
                    @elseif($item->system_status === 'partial_delivered')
                        <span class="badge badge-info">{{ __('Partial Delivered') }}</span>
                    @elseif($item->system_status === 'cancelled')
                        <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                    @else
                        <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                    @endif
                </td>
                <td>{{ $item->customer->name ?? 'N/A' }}</td>
                
                <td>{{ number_format($item->grand_total, 2) . ' ' . $general->site_currency ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No record sales found</td>
            </tr>
        @endforelse
    </tbody>
</table>
