<?php

namespace App\Transformers;

class SalesTransformer extends BaseTransformer
{
    const SALES_HEADERS = [
        'created_at',       // Corresponds to $sale->created_at->format('Y-m-d H:i:s')
        'created_by',       // Corresponds to $sale->user->name ?? 'N/A'
        'lead_by',          // Corresponds to $sale->lead->name ?? 'N/A'
        'name',             // Corresponds to $sale->customer->name ?? 'N/A'
        'phone',            // Corresponds to $sale->customer->phone ?? 'N/A'
        'invoice_no',       // Corresponds to $sale->invoice_no ?? 'N/A'
        'system_status',    // Corresponds to $sale->system_status ?? 'N/A'
        'consignments_id', // Corresponds to $sale->consignment_id ?? 'N/A'
        'status',         // Corresponds to $sale->status ?? 'N/A'
        'COD',              // Corresponds to $sale->due_amount ?? 'N/A'
        'grand_total',      // Corresponds to $sale->grand_total ?? 'N/A'
        'paid',             // Corresponds to $sale->paid ?? 'N/A'
        'platform',         // Corresponds to $sale->platform ?? 'N/A'
        'payment_method',   // Corresponds to $sale->payment_method ?? 'N/A'
        'payment_status',   // Corresponds to $sale->payment_status ?? 'N/A'
        'warehouse',
    ];

    /**
     * Transform the sales model into a CSV row.
     *
     * @param  \App\Models\Sale  $sale
     * @return array
     */
    public function transform($sale)
    {
        return [
            $sale->created_at->format('Y-m-d'),
            $sale->user->name ?? 'N/A',
            $sale->lead->name ?? 'N/A',
            $sale->customer->name ?? 'N/A',
            $sale->customer->phone ?? 'N/A',
            $sale->invoice_no ?? 'N/A',
            $sale->system_status ?? 'N/A',
            $sale->consignment_id ?? 'N/A',
            $sale->status ?? 'N/A',
            $sale->due_amount ?? 'N/A',
            $sale->grand_total ?? 'N/A',
            $sale->paid ?? 'N/A',
            $sale->platform ?? 'N/A',
            $sale->paymentMethod->name ?? 'N/A',
            match ($sale->payment_status) {
                2 => 'Due',
                1 => 'Paid',
                3 => 'Partial',
                default => 'N/A'
            },
            $sale->warehouse->name ?? 'N/A',
        ];
    }

    /**
     * Get the headers for the CSV.
     *
     * @return array
     */
    public function getHeaders()
    {
        return self::SALES_HEADERS;
    }
}
