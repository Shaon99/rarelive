<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Transaction Date</th>
            <th>To Account</th>
            <th>Previous Balance</th>
            <th>Amount Added</th>            
            <th>Balance</th>
            <th>Note</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay-new" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse($fund_transactions as $key => $transaction)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $transaction->transaction_date }}</td>
                <td class="text-uppercase">{{ $transaction->toAccount?->name . ' (' . 'AC: ' . $transaction->toAccount?->account_number . ')' ?? '-' }}
                </td>
                <td>{{ number_format($transaction->account_balance_was, 2) }} {{ $general->site_currency }}</td>
                <td>{{ number_format($transaction->amount, 2) }} {{ $general->site_currency }}</td>
                <td>{{ number_format($transaction->account_balance_was + $transaction->amount, 2) }} {{ $general->site_currency }}</td>
                <td>{{ $transaction->note ?? '--' }}</td>
                <td>{{ $transaction->created_at->format('d M, Y H:i a') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No Transactions Found</td>
            </tr>
        @endforelse
    </tbody>
</table>
@if ($fund_transactions->hasPages())
    {{ $fund_transactions->links('backend.partial.paginate') }}
@endif
