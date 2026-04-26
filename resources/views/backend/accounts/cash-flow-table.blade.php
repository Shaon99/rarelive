<h6 class="f-12">
    {{ $general->sitename }} Money Flow Summary<br>
    Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
</h6>
<div class="row border-top">
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-overlay-text text-center">please wait...</div>
    </div>
    <!-- Left Column - CASH INFLOW -->
    <div class="col-6 border-r">
        <h6 class="f-12 mt-2">MONEY INFLOW</h6>
        <div></div>
        @forelse ($paymentAccounts as $item)
            <div class="mb-2">
                <div class="dotted-border">{{ $item->name }}</div>
                <div class="no-record">
                    {{ number_format($inflowByMethod[$item->id] ?? 0, 2) }}
                </div>
            </div>
        @empty
        @endforelse
        <div class="total-box my-3">
            TOTAL ----<span class="amount">{{ number_format($totalSalesInflow, 2) }} {{ @$general->site_currency }}</span>
        </div>
    </div>
    <!-- Right Column - CASH OUTFLOW -->
    <div class="col-6">
        <h6 class="f-12 mt-2">MONEY OUTFLOW</h6>
        @forelse ($paymentAccounts as $item)
            <div class="mb-2">
                <div class="dotted-border">{{ $item->name }}</div>
                <div class="no-record">
                    {{ number_format($outflowByMethod[$item->id] ?? 0, 2) }}
                </div>
            </div>
        @empty
        @endforelse
        <div class="total-box my-3">
            TOTAL ----<span class="amount">{{ number_format($totalOutflow, 2) }} {{ @$general->site_currency }}</span>
        </div>
    </div>
    <div class="col-6 mt-3">
        <!-- Closing Cash Calculation -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-dark  text-uppercase">Closing Balance</th>
                    <th class="text-dark text-right text-uppercase">( Opening Balance + Total Inflow - Total Outflow )
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td class="text-right">{{ number_format($openingCash - ($totalSalesInflow-$totalOutflow), 2) }} +
                        {{ number_format($totalSalesInflow, 2) }} - {{ number_format($totalOutflow, 2) }} {{ $general->site_currency }}</td>
                </tr>
                <tr>
                    <td colspan="1" class="text-right"><strong>Current Balance:</strong></td>
                    <td class="text-right"><strong>{{ number_format($closingCash, 2) }}
                            {{ $general->site_currency }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
