<?php

namespace App\Transformers;

class ExpenseReportTransformer extends BaseTransformer
{
    const EXPENSE_REPORT_HEADERS = [
        'created_at',       // Corresponds to $expense->created_at->format('Y-m-d H:i:s')
        'category',          // Corresponds to $expense->expenseCategory->name ?? 'N/A'
        'amount',            // Corresponds to $expense->amount ?? 'N/A'
        'date',             // Corresponds to $expense->date ?? 'N/A'
        'note',             // Corresponds to $expense->note ?? 'N/A'
    ];

    /**
     * Transform the sales model into a CSV row.
     *
     * @param  \App\Models\Expense  $expense
     * @return array
     */
    public function transform($expense)
    {
        return [
            $expense->created_at->format('Y-m-d H:i:s'),
            $expense->expenseCategory->name ?? 'N/A',
            $expense->amount ?? 'N/A',
            $expense->date ?? 'N/A',
            $expense->note ?? 'N/A',
        ];
    }

    /**
     * Get the headers for the CSV.
     *
     * @return array
     */
    public function getHeaders()
    {
        return self::EXPENSE_REPORT_HEADERS;
    }
}
