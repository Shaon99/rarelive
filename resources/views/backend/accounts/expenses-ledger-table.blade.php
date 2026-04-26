<h6 class="f-12">
    {{ $general->sitename }} Expenses Ledger<br>
    From Date: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }},
    To Date: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
</h6>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Invoice No</th>
            <th>Description</th>
            <th>DR</th>
            <th>CR</th>
            <th>Balance</th>
        </tr>
    </thead>

    <tbody>
        <div id="loading-overlay" class="loading-overlay">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        <tr>
            <!-- Opening Balance -->
            <td>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</td>
            <td></td>
            <td><strong>Opening Balance</strong></td>
            <td>-</td>
            <td>-</td>
            <td><strong>{{ number_format($openingBalance, 2) }}</strong></td>
        </tr>

        <!-- Transactions -->
        @forelse ($transactions as $entry)
            <tr>
                <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                <td>{{ $entry->invoice_no ?? '-' }}</td>
                <td>{{ $entry->note ?? '-' }}
                    [{{ $entry->payment_methods }}]
                </td>

                <td class="text-success">{{ $entry->debit_amount > 0 ? number_format($entry->debit_amount, 2) : '-' }}</td>
                <td class="text-danger">{{ $entry->credit_amount > 0 ? number_format($entry->credit_amount, 2) : '-' }}</td>
                <td><strong>{{ number_format($entry->balance, 2) }}</strong></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No transactions found for the selected period.</td>
            </tr>
        @endforelse

        <!-- Closing Totals -->
        <tr>
            <td colspan="3" class="text-right"><strong>Closing Balance:</strong></td>
            <td class="text-success"><strong>{{ number_format($totals->total_debit ?? 0, 2) }} ({{ $general->site_currency }})</strong>
            </td>
            <td class="text-danger"><strong>{{ number_format($totals->total_credit ?? 0, 2) }} ({{ $general->site_currency }})</strong>
            </td>
            <td><strong>{{ number_format($closingBalance ?? 0, 2) }} ({{ $general->site_currency }})</strong></td>
        </tr>
    </tbody>
</table>
