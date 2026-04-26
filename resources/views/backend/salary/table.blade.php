<table class="table tab-bordered">
    <thead>
        <tr>
            <th>SL</th>
            <th>Actions</th>
            <th>Status</th>
            <th>Employee</th>
            <th>Basic Salary</th>
            <!--<th>Others</th>-->
            <th>Over Time</th>
            <th>Bonus</th>
            <th>Deductions</th>
            <th>Advance</th>
            <th>Total Salary</th>
            <th>Paid Amount</th>
            <th>Due Amount</th>
            <th>Salary For</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse($salaries as $salary)
            @php
                $status = $salary->status;

                if ($status == 'paid') {
                    $badgeClass = 'success';
                    $statusText = 'Paid';
                } elseif ($status == 'partial') {
                    $badgeClass = 'warning';
                    $statusText = 'Partially Paid';
                } else {
                    $badgeClass = 'danger';
                    $statusText = 'Due';
                }
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    @if ($status == 'due')
                        @if (auth()->guard('admin')->user()->can('salary_edit'))
                            <a href="{{ route('admin.salaries.edit', $salary->id) }}" class="btn btn-primary btn-sm mr-1">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        @endif
                        @if (auth()->guard('admin')->user()->can('salary_delete'))
                            <button class="btn btn-danger btn-sm delete mr-1"
                                data-href="{{ route('admin.salaries.destroy', $salary->id) }}" data-toggle="tooltip"
                                title="Delete" type="button">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    @endif
                    <a href="{{ route('admin.salary.show', $salary->id) }}" class="btn btn-success btn-sm mr-1">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if (auth()->guard('admin')->user()->can('salary_payment'))
                        @if ($status == 'partial' || $status == 'due')
                            <button class="btn btn-info btn-sm btn-icon due mr-1"
                                data-href="{{ route('admin.salary.salaryPayment', $salary->id) }}"
                                data-due="{{ $salary->total_salary - $salary->payments->sum('amount_paid') }}"
                                data-name="{{ $salary->employee->name . ' Salary Due payment ' }} {{ \Carbon\Carbon::parse($salary->salary_for)->format('F, Y') }}"
                                data-toggle="tooltip" title="Salary Due Payment" type="button">
                                <i class="fas fa-credit-card"></i>
                            </button>
                        @endif
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $badgeClass }}">{{ $statusText }}</span>
                </td>
                <td>{{ $salary->employee->employee_name }}</td>
                <td>{{ currency_format($salary->basic_salary) }}</td>
                <!--<td>{{ currency_format($salary->others) }}</td>-->
                <td>{{ currency_format($salary->over_time) }}</td>
                <td>{{ currency_format($salary->bonus) }}</td>
                <td>{{ currency_format($salary->deductions) }}</td>
                <td>{{ currency_format($salary->advance) }}</td>
                <td>{{ currency_format($salary->total_salary) }}</td>
                <td>{{ currency_format($salary->payments->sum('amount_paid')) }}</td>
                <td>{{ currency_format($salary->total_salary - $salary->payments->sum('amount_paid')) }}</td>
                <td>{{ \Carbon\Carbon::parse($salary->salary_for)->format('F, Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">{{ __('No record available') }}</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="9" class="text-right">Total:</th>
            <th>{{ currency_format($salaries->sum('total_salary')) }}</th>
            <th>
                {{ currency_format($salaries->sum(function ($salary) {return $salary->payments->sum('amount_paid');})) }}
            </th>
            <th>
                {{ currency_format($salaries->sum('total_salary') -$salaries->sum(function ($salary) {return $salary->payments->sum('amount_paid');})) }}
            </th>
            <th></th>
        </tr>
    </tfoot>
</table>
@if ($salaries->hasPages())
    {{ $salaries->links('backend.partial.paginate') }}
@endif
