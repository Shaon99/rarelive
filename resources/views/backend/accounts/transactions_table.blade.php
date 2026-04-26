<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Transaction Date</th>
            <th>From Account</th>
            {{-- <th>Balance Was</th> --}}
            <th>To Account</th>
            <th>Amount</th>
            <th>Note</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse($transactions as $key => $transaction)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $transaction->transaction_date }}</td>
                <td class="text-uppercase">
                    {{ optional($transaction->fromAccount)->name && optional($transaction->fromAccount)->account_number
                        ? optional($transaction->fromAccount)->name . ' (AC: ' . optional($transaction->fromAccount)->account_number . ')'
                        : 'SteadFast Account' }}
                </td>
                </td>
                {{-- <td>{{ $transaction->account_balance_was ?? 0.0 }} {{ $general->site_currency }}</td> --}}
                <td class="text-uppercase">
                    {{ $transaction->toAccount->name . ' (' . 'AC: ' . $transaction->toAccount->account_number . ')' ?? '-' }}
                </td>
                <td>{{ number_format($transaction->amount, 2) }} {{ $general->site_currency }}</td>
                <td>{{ $transaction->note ?? '--' }}</td>
                <td>{{ $transaction->created_at->format('d M, Y H:i a') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No Transactions Found</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($transactions->hasPages())
    {{ $transactions->links('backend.partial.paginate') }}
@endif
