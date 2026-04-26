<div class="text-center mb-3">
    <h6>{{ $general->sitename }} Profit/Loss Report</h6>
    <p>From: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
       To: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
</div>

<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-overlay-text text-center">Please wait...</div>
</div>

<table class="table table-bordered">
    <tr>
        <th>Total Sales</th>
        <td>{{ currency_format($salesTotal) }}</td>
    </tr>
    <tr>
        <th>Courier Charges</th>
        <td>{{ currency_format($totalCourierCost) }}</td>
    </tr>
    <tr>
        <th>Sales Total (Net Sales)</th>
        <td>{{ currency_format($salesTotal - $totalCourierCost) }}</td>
    </tr>
    <tr>
        <th>Purchase Total (COGS)</th>
        <td>{{ currency_format($purchaseCOGS) }}</td>
    </tr>
    <tr>
        <th><strong>Gross Profit</strong></th>
        <td><strong>{{ currency_format($grossProfit) }}</strong></td>
    </tr>
    <tr>
        <td>General Expenses</td>
        <td>{{ currency_format($generalExpenses) }}</td>
    </tr>
    <tr>
        <td>Salary Expenses</td>
        <td>{{ currency_format($salaryExpenses) }}</td>
    </tr>
    <tr>
        <td><strong>Total Expenses</strong></td>
        <td><strong>{{ currency_format($generalExpenses + $salaryExpenses) }}</strong></td>
    </tr>
    <tr>
        <th><strong>Net Profit</strong></th>
        <td><strong>{{ currency_format($netProfit) }}</strong></td>
    </tr>
</table>
