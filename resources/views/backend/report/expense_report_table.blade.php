<table class="table table-bordered">

    <thead>
        <tr>
            <th>{{ __('#') }}</th>
            <th>{{ __('Created At') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Payment Account') }}</th>
            <th>{{ __('Remark') }}</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse ($expenseReports as $item)
            <tr>
                <td>{{ $loop->iteration + ($expenseReports->currentPage() - 1) * $expenseReports->perPage() }}</td>
                <td>{{ $item->created_at->format('d M, y') }}</td>
                <td>
                    {{ $item->expenseCategory->name ?? 'N/A' }}
                </td>
                <td>{{ $item->date }}</td>
                <td>{{ number_format($item->amount, 2). ' ' . $general->site_currency }}
                <td>
                    @if ($item->paymentAccount)
                        {{ $item->paymentAccount->name }}
                        @if ($item->paymentAccount->account_number)
                            (AC: {{ $item->paymentAccount->account_number }})
                        @endif
                    @else
                        N/A
                    @endif
                </td>

                <td>{{ $item->note ?? 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">no record found</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end"><strong>Total: {{ $expenseReports->count() }}</strong></td>
            <td><strong>{{ number_format($expenseReports->sum('amount'), 2). ' ' . $general->site_currency }}</strong></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@if ($expenseReports->hasPages())
    {{ $expenseReports->links('backend.partial.paginate') }}
@endif
