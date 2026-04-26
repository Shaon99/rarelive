<h6 class="f-12 text-center mb-3">
    {{ $general->sitename }} Sales Ledger<br>
    From {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to
    {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
</h6>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th class="text-white">Date</th>
                <th class="text-white">Invoice No</th>
                <th class="text-white">Description</th>
                <th class="text-right text-white">Debit ({{ $general->site_currency }})</th>
                <th class="text-right text-white">Credit ({{ $general->site_currency }})</th>
                <th class="text-right text-white">Balance ({{ $general->site_currency }})</th>
            </tr>
        </thead>
        <tbody>
            <div id="loading-overlay" class="loading-overlay">
                <div class="loading-overlay-text text-center">please wait...</div>
            </div>

            <!-- Opening Balance -->
            <tr class="table-info">
                <td>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y h:i A') }}</td>
                <td>-</td>
                <td><strong>Opening Balance</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">
                    <strong>
                        {{ number_format(abs($openingBalance), 2) }}
                        @if ($openingBalance > 0)
                            Dr
                        @elseif($openingBalance < 0)
                            Cr
                        @endif
                    </strong>
                </td>
            </tr>

            <!-- Transactions Grouped by Invoice -->
            @php $lastInvoice = null; @endphp
            @forelse ($transactions as $entry)
                <tr class="{{ $lastInvoice !== $entry->invoice_no ? 'border-top' : '' }}">
                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y h:i A') }}</td>
                    <td>
                        @if ($lastInvoice !== $entry->invoice_no)
                            {{ $entry->invoice_no ?? '-' }}
                            @php $lastInvoice = $entry->invoice_no; @endphp
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-capitalize">
                        {{ $entry->note ?? '-' }} [{{ $entry->payment_methods ?? 'Due' }}]
                    </td>
                    <td class="text-right text-success">
                        {{ $entry->debit_amount > 0 ? number_format($entry->debit_amount, 2) : '-' }}
                    </td>
                    <td class="text-right text-danger">
                        {{ $entry->credit_amount > 0 ? number_format($entry->credit_amount, 2) : '-' }}
                    </td>
                    <td class="text-right">
                        <strong>
                            {{ number_format(abs($entry->balance), 2) }}
                            @if ($entry->balance > 0)
                                Dr
                            @elseif($entry->balance < 0)
                                Cr
                            @endif
                        </strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No transactions found for the selected period.
                    </td>
                </tr>
            @endforelse

            <!-- Closing Totals -->
            <tr class="table-secondary">
                <td colspan="3" class="text-right"><strong>Closing Totals:</strong></td>
                <td class="text-right text-success">
                    <strong>{{ number_format($totals->total_debit ?? 0, 2) }}</strong>
                </td>
                <td class="text-right text-danger">
                    <strong>{{ number_format($totals->total_credit ?? 0, 2) }}</strong>
                </td>
                <td class="text-right">
                    <strong>
                        {{ number_format(abs($closingBalance ?? 0), 2) }}
                        @if (($closingBalance ?? 0) > 0)
                            Dr
                        @elseif(($closingBalance ?? 0) < 0)
                            Cr
                        @endif
                    </strong>
                </td>
            </tr>
        </tbody>
    </table>
</div>
