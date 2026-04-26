<table class="table table-bordered">
    <thead>
        <tr>
            <th>{{ __('#') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Invoice') }}</th>
            <th>{{ __('Product') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Created By') }}</th>
            @if ($general->pos_lead_on_off == 1)
                <th>Lead By</th>
            @endif
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('City') }}</th>
            <th title="Status">Status</th>
            @if ($general->enable_online_deliver)
                <th title="Courier Name">Courier Name</th>
                <th title="Consignment Id">Consignments ID</th>
                <th title="Courier Delivery Status">Delivery Status</th>
                <th title="Courier COD Charge">COD Charge</th>
                <th title="Courier Delivery Cost">Delivery Cost</th>
            @endif
            <th>{{ __('Sale Price') }}</th>
            <th>{{ __('Discount') }}</th>
            <th title="Cash On Delivery">Due</th>
            <th>Paid</th>
            <th>Grand Total</th>
            @if ($general->enable_online_deliver)
                <th>Courier Cost</th>
                <th title="Cash In Hand">CIH</th>
            @endif
            @if ($general->pos_platform_on_off == 1)
                <th>Platform</th>
            @endif
            <th>Payment Method</th>
            <th>Payment Status</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse ($salesReports as $item)
            <tr>
                <td>{{ $loop->iteration + ($salesReports->currentPage() - 1) * $salesReports->perPage() }}</td>
                <td>{{ $item->sale->created_at->format('d M, y') }}</td>
                <td>
                    <a href="{{ route('admin.invoice', $item->sale->id) }}">
                        {{ $item->sale->invoice_no }}
                    </a>
                </td>

                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->sale->user?->name }}</td>
                @if ($general->pos_lead_on_off == 1)
                    <td>{{ $item->sale->lead?->employee_name ?? 'N/A' }}</td>
                @endif
                <td>{{ $item->sale->customer?->name }}</td>
                <td>{{ $item->sale->customer?->phone }}</td>
                <td>
                    @php
                        $cities = $item->sale->customer->addressBooks->pluck('city')->filter()->implode(', ');
                    @endphp
                    {{ $cities ?: 'N/A' }}
                </td>
                <td>
                    @if ($item->sale->system_status === 'pending')
                        <span class="badge badge-warning">{{ __('Shipping') }}</span>
                    @elseif($item->sale->system_status === 'completed')
                        <span class="badge badge-success">{{ __('Delivered') }}</span>
                    @elseif($item->sale->system_status === 'cancelled')
                        <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                    @endif
                </td>
                @if ($general->enable_online_deliver)
                    <td>
                        @if ($item->courier_name === 'steadfast')
                            <span class="badge badge-success">{{ $item->courier_name }}</span>
                        @elseif ($item->courier_name === 'pathao')
                            <span class="badge badge-danger">{{ $item->courier_name }}</span>
                        @else
                            <span class="badge badge-primary">{{ $item->courier_name ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td>
                        @php $consignmentId = $item->consignment_id ?? 'N/A'; @endphp
                        <span class="copyable selectable" onclick="copyToClipboard(this, '{{ $consignmentId }}')">
                            {{ $consignmentId }}
                            @if ($consignmentId != 'N/A')
                                <i class="fas fa-copy copy-icon text-success ml-2"></i>
                            @endif
                        </span>
                    </td>

                    <td>
                        @if ($item->status === 'pending' || $item->status === 'in_review')
                            <span class="badge badge-warning">{{ Str::headline($item->status ?? 'N/A') }}</span>
                        @elseif($item->status === 'delivered')
                            <span class="badge badge-success">{{ Str::headline($item->status ?? 'N/A') }}</span>
                        @elseif($item->status === 'cancelled' || $item->status === 'unknown')
                            <span class="badge badge-danger">{{ Str::headline($item->status ?? 'N/A') }}</span>
                        @elseif($item->status === 'partial_delivered')
                            <span class="badge badge-info">{{ Str::headline($item->status ?? 'N/A') }}</span>
                        @else
                            <span class="badge badge-primary">{{ Str::headline($item->status ?? 'N/A') }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($item->cod_charge, 2) ?? '0.00' }}</td>
                    <td>{{ number_format($item->shipping_cost, 2) ?? '0.00' }}</td>
                @endif
                <td>{{ number_format($item->sale_price, 2) }}
                <td>{{ number_format($item->discount, 2) . ' ' . ($item->discount_type === 'percentage' ? ' %' : $general->site_currency) }}
                <td>{{ number_format($item->sale->due_amount, 2) }}</td>
                <td>{{ number_format($item->sale->paid_amount, 2) }}</td>

                <td>{{ number_format($item->sale->grand_total, 2) }}</td>
                @if ($general->enable_online_deliver)
                    <td>{{ number_format($item->cod_charge + $item->shipping_cost, 2) }}</td>
                    <td>{{ number_format($item->paid_amount - ($item->cod_charge + $item->shipping_cost), 2) }}
                @endif
                @if ($general->pos_platform_on_off == 1)
                    <td>
                        @if ($item->platform === 'facebook')
                            <span class="badge badge-primary">{{ __('Facebook') }}</span>
                        @elseif($item->platform === 'whatsapp')
                            <span class="badge badge-success">{{ __('Whatsapp') }}</span>
                        @elseif($item->platform === 'others')
                            <span class="badge badge-info">{{ __('Others') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('N/A') }}</span>
                        @endif
                    </td>
                @endif
                <td class="text-center">
                    @php
                        $type = $item->sale->paymentMethod->type ?? '';
                    @endphp

                    @switch($type)
                        @case('CASH')
                            <span class="badge badge-success">{{ $item->sale->paymentMethod->name ?? 'N/A' }}</span>
                        @break

                        @case('STEADFAST')
                            <span class="badge badge-warning">{{ $item->sale->paymentMethod->name ?? 'N/A' }} (COD)</span>
                        @break

                        @case('BANK')
                            <span class="badge badge-info">{{ $item->sale->paymentMethod->name ?? 'N/A' }}</span>
                        @break

                        @case('MFS')
                            <span class="badge badge-primary">{{ $item->sale->paymentMethod->name ?? 'N/A' }}</span>
                        @break

                        @default
                            <span class="badge badge-dark">{{ $item->sale->paymentMethod->name ?? 'COD' }}</span>
                    @endswitch
                </td>
                <td>
                    @if ($item->sale->payment_status == 2)
                        <span class="badge badge-danger">{{ __('Due') }}</span>
                    @elseif($item->sale->payment_status == 1)
                        <span class="badge badge-success">{{ __('Paid') }}</span>
                    @endif
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center">no record found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><strong>Total: {{ $salesReports->count() }}</strong></td>
                <td></td>
                <td></td>
                @if ($general->pos_lead_on_off == 1)
                    <td></td>
                @endif
                <td colspan="4"></td>
                @if ($general->enable_online_deliver)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><strong>{{ number_format($salesReports->sum('cod_charge'), 2) }}
                            {{ $general->site_currency }}</strong></td>
                    <td><strong>{{ number_format($salesReports->sum('shipping_cost'), 2) }}
                            {{ $general->site_currency }}</strong></td>
                @endif
                <td><strong>{{ number_format($salesReports->sum('sale_price'), 2) }}
                        {{ $general->site_currency }}</strong></td>
                <td><strong>{{ number_format($salesReports->sum('discount'), 2) }}
                        {{ $general->site_currency }}</strong></td>
                <td><strong>{{ number_format($salesReports->sum('sale.due_amount'), 2) }}
                        {{ $general->site_currency }}</strong></td>
                <td><strong>{{ number_format($salesReports->sum('sale.paid_amount'), 2) }}
                        {{ $general->site_currency }}</strong></td>
                <td><strong>{{ number_format($salesReports->sum('sale.grand_total'), 2) }}
                        {{ $general->site_currency }}</strong></td>
                @if ($general->enable_online_deliver)
                    <td><strong>{{ number_format($salesReports->sum('cod_charge') + $salesReports->sum('shipping_cost'), 2) }}
                            {{ $general->site_currency }}</strong></td>
                    <td><strong>{{ number_format($salesReports->sum('paid_amount') - ($salesReports->sum('cod_charge') + $salesReports->sum('shipping_cost')), 2) }}
                            {{ $general->site_currency }}</strong></td>
                @endif
                @if ($general->pos_platform_on_off == 1)
                    <td></td>
                @endif
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @if ($salesReports->hasPages())
        {{ $salesReports->links('backend.partial.paginate') }}
    @endif
