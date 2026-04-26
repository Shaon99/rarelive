<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Transaction Date</th>
            <th>From Account</th>
            <th>Previous Balance</th>
            <th>Withdraw Amount</th>
            <th>Balance After</th>
            <th>Note</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @forelse($withdraw_transactions as $key => $transaction)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M, Y') }}</td>
                <td class="text-uppercase">
                    {{ $transaction->toAccount->name ?? '-' }}
                    @if ($transaction->toAccount?->account_number)
                        (AC: {{ $transaction->toAccount->account_number }})
                    @endif
                </td>
                <td>{{ number_format($transaction->account_balance_was, 2) }} {{ $general->site_currency }}</td>
                <td class="text-danger">
                    -{{ number_format($transaction->amount, 2) }} {{ $general->site_currency }}
                </td>
                <td>
                    {{ number_format($transaction->account_balance_was - $transaction->amount, 2) }} {{ $general->site_currency }}
                </td>
                <td>{{ $transaction->note ?? '--' }}</td>
                <td>{{ $transaction->created_at->format('d M, Y h:i A') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No Transactions Found</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($withdraw_transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $withdraw_transactions->hasPages())
    {{ $withdraw_transactions->links('backend.partial.paginate') }}
@endif
