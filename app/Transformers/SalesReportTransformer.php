<?php

namespace App\Transformers;

class SalesReportTransformer extends BaseTransformer
{
    const SALES_REPORT_HEADERS = [
        'created_at',       // Corresponds to $sale->created_at->format('Y-m-d H:i:s')
        'created_by',       // Corresponds to $sale->user->name ?? 'N/A'
        'name',             // Corresponds to $sale->customer->name ?? 'N/A'
        'phone',            // Corresponds to $sale->customer->phone ?? 'N/A'
        'invoice_no',       // Corresponds to $sale->invoice_no ?? 'N/A'
        'product',
        'quantity',
        'sale_price',
        'system_status',    // Corresponds to $sale->system_status ?? 'N/A'
        'C_id',             // Corresponds to $sale->consignment_id ?? 'N/A'
        'C_Status',         // Corresponds to $sale->status ?? 'N/A'
        'Tr-id',            // Corresponds to $sale->tracking_code ?? 'N/A'
        'COD',              // Corresponds to $sale->due_amount ?? 'N/A'
        'grand_total',      // Corresponds to $sale->grand_total ?? 'N/A'
        'paid',             // Corresponds to $sale->paid ?? 'N/A'
        'platform',         // Corresponds to $sale->platform ?? 'N/A'
        'payment_method',   // Corresponds to $sale->payment_method ?? 'N/A'
        'payment_status',   // Corresponds to $sale->payment_status ?? 'N/A'
    ];

    /**
     * Transform the sales model into a CSV row.
     *
     * @param  \App\Models\SalesProduct  $salesProduct
     * @return array
     */
    public function transform($salesProduct)
    {
        return [
            $salesProduct->sale->created_at->format('Y-m-d'),
            $salesProduct->sale->user->name ?? 'N/A',
            $salesProduct->sale->customer->name ?? 'N/A',
            $salesProduct->sale->customer->phone ?? 'N/A',
            $salesProduct->sale->invoice_no ?? 'N/A',
            $salesProduct->product->name ?? 'N/A',
            $salesProduct->quantity ?? 'N/A',
            $salesProduct->sale_price ?? 'N/A',
            $salesProduct->sale->system_status ?? 'N/A',
            $salesProduct->sale->consignment_id ?? 'N/A',
            $salesProduct->sale->status ?? 'N/A',
            $salesProduct->sale->tracking_code ?? 'N/A',
            $salesProduct->sale->due_amount ?? 'N/A',
            $salesProduct->sale->grand_total ?? 'N/A',
            $salesProduct->sale->paid ?? 'N/A',
            $salesProduct->sale->platform ?? 'N/A',
            $salesProduct->sale->payment_method ?? 'N/A',
            $salesProduct->sale->payment_status ?? 'N/A',
        ];
    }

    /**
     * Get the headers for the CSV.
     *
     * @return array
     */
    public function getHeaders()
    {
        return self::SALES_REPORT_HEADERS;
    }
}
