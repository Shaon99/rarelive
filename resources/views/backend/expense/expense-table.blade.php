<table class="table">
    <thead>
        <tr>
            <th>{{ __('SL') }}</th>
            <th>{{ __('Invoice NO') }}</th>
            <th>{{ __('Expense category') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Payment Account') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Remark') }}</th>
            <th>{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse ($expenses as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->invoice_no ?? 'N/A' }}</td>
                <td>{{ $item->expenseCategory->name ?? 'N/A' }}</td>
                <td>{{ number_format($item->amount, 2) }} {{ $general->site_currency }}</td>
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

                <td>{{ $item->date ?? 'N/A' }}</td>
                <td>{{ $item->note ?? 'N/A' }}</td>
                <td>
                    @if (auth()->guard('admin')->user()->can('expense_edit'))
                        <a href="{{ route('admin.expense.edit', $item->id) }}" class="btn btn-primary btn-sm mr-1"
                            data-toggle="tooltip" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    @endif
                    @if (auth()->guard('admin')->user()->can('expense_delete'))
                        <button class="btn btn-danger btn-sm delete mr-1"
                            data-href="{{ route('admin.expense.destroy', $item->id) }}" data-toggle="tooltip"
                            title="Delete" type="button">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">{{ __('No data available') }}</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right"><strong>{{ __('Total Amount') }}</strong></td>
            <td><strong>
                {{ number_format($expenses->sum('amount'), 2) }} {{ $general->site_currency }}
            </strong></td>
            <td colspan=""></td>
        </tr>
    </tfoot>
</table>

@if ($expenses->hasPages())
    {{ $expenses->links('backend.partial.paginate') }}
@endif
