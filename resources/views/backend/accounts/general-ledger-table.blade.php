<h6 class="f-12">
    {{ $general->sitename }} General Ledger<br>
    From Date: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }},
    To Date: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
</h6>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Reference</th>
            <th>Description</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Running Balance</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay">
            <div class="loading-overlay-text text-center">Please wait...</div>
        </div>

        <!-- Opening Balance Row -->
        <tr>
            <td colspan="6"><strong>Opening Balance</strong></td>
            <td>
                <strong>
                    {{ number_format(abs((float) $openingBalance), 2) }}
                    @if ((float) $openingBalance > 0)
                        Dr
                    @elseif((float) $openingBalance < 0)
                        Cr
                    @endif
                </strong>
            </td>
        </tr>

        <!-- Loop through transactions -->
        @forelse ($transactions as $index => $trx)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->created_at)->format('Y-m-d H:i') }}</td>
                <td>{{ Str::headline($trx->transaction_type) ?? '-' }}</td>
                <td class="note-cell">{{ $trx->note ?? '-' }} [{{ $trx->paymentMethod->name??'COD' }}]</td>
                <td class="text-success">{{ $trx->debit ? number_format((float) $trx->debit, 2) : '-' }}</td>
                <td class="text-danger">{{ $trx->credit ? number_format((float) $trx->credit, 2) : '-' }}</td>
                <td>
                    {{ number_format(abs((float) $trx->running_balance), 2) }}
                    @if ((float) $trx->running_balance > 0)
                        Dr
                    @elseif((float) $trx->running_balance < 0)
                        Cr
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No transactions found.</td>
            </tr>
        @endforelse
        <!-- Total Row -->
        <tr class="bg-light">
            <td colspan="4" class="text-end"><strong>Total</strong></td>
            <td class="text-success"><strong>{{ number_format((float) $totalDebit, 2) }} ({{ $general->site_currency }})</strong></td>
            <td class="text-danger"><strong>{{ number_format((float) $totalCredit, 2) }} ({{ $general->site_currency }})</strong></td>
            <td>
                <strong>
                    {{ number_format(abs((float) $closingBalance), 2) }}
                    @if ((float) $closingBalance > 0)
                        Dr
                    @elseif((float) $closingBalance < 0)
                        Cr
                    @endif
                    ({{ $general->site_currency }})
                </strong>
            </td>
        </tr>
    </tbody>
</table>
