<?php

namespace App\Jobs;

use App\Models\SalesProduct;
use App\Transformers\BaseTransformer;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateSalesReportCsvJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;

    protected $transformer;

    protected $batch;

    protected $fileName;

    public $tries = 3;

    public $timeout = 3600;

    public function __construct($filters, $fileName, BaseTransformer $transformer)
    {
        $this->filters = $filters;
        $this->fileName = $fileName;
        $this->transformer = $transformer;
    }

    public function handle()
    {
        // Create a temporary file to write CSV data
        $fullFilePath = storage_path('app/public/'.$this->fileName);
        $file = fopen($fullFilePath, 'w');

        // Insert headers (using the transformer headers)
        fputcsv($file, $this->transformer->getHeaders());

        // Query to get the filtered sales products
        $query = SalesProduct::with(['product:id,name', 'sale']);

        // Apply filters directly in the query
        $this->applyFilters($query, $this->filters);

        // Total sales count for progress tracking
        $totalSales = $query->count(); // Apply filters before counting
        $processedSales = 0;

        // Process data in chunks
        $query->chunk(100, function ($salesProducts) use ($file, $totalSales, &$processedSales) {
            foreach ($salesProducts as $salesProduct) {
                // Transform each sale using the SalesReportTransformer
                $row = $this->transformer->transform($salesProduct);
                fputcsv($file, $row); // Write each transformed row to the file

                $processedSales++;
                $progress = (int) (($processedSales / $totalSales) * 100); // Calculate progress percentage
                $this->updateProgress($progress); // Update batch progress
            }
        });

        // Store the CSV file path
        Storage::disk('public')->put($this->fileName, file_get_contents($fullFilePath));

        // Optionally, you can send a notification or update a record to notify the user that the file is ready
        return $this->fileName;
    }

    private function applyFilters($query, $filters)
    {
        // Apply filters on SalesProduct
        if (isset($filters['products'])) {
            $query->whereIn('product_id', $filters['products']);
        }

        // Apply filters on related Sale model
        $query->whereHas('sale', function ($saleQuery) use ($filters) {
            // Filter by created_by (user_id)
            if (isset($filters['created_by'])) {
                $saleQuery->whereIn('user_id', $filters['created_by']);
            }

            if (isset($filters['lead_by'])) {
                $saleQuery->whereIn('lead_id', $filters['lead_by']);
            }
            // Filter by system_status
            if (isset($filters['s_status'])) {
                $saleQuery->whereIn('system_status', $filters['s_status']);
            }

            // Filter by courier_status
            if (isset($filters['courier_status'])) {
                $saleQuery->whereIn('courier_status', $filters['courier_status']);
            }

            // Filter by platform
            if (isset($filters['platform'])) {
                $saleQuery->whereIn('platform', $filters['platform']);
            }

            // Filter by payment_method (payment_by)
            if (isset($filters['payment_by'])) {
                $saleQuery->whereIn('payment_method', $filters['payment_by']);
            }

            // Filter by payment_status
            if (isset($filters['payment_status'])) {
                $saleQuery->whereIn('payment_status', $filters['payment_status']);
            }

            // Filter by date_range (created_at)
            if (isset($filters['date_range'])) {
                [$startDate, $endDate] = explode(' - ', $filters['date_range']);
                $saleQuery->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
            }

            // Apply search filter on customer info, invoice, consignment id, or tracking id
            if (isset($filters['search'])) {
                $search = $filters['search'];
                $saleQuery->where(function ($query) use ($search) {
                    $query->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%");
                    })
                        ->orWhere('invoice_no', 'like', "%$search%")
                        ->orWhere('consignment_id', 'like', "%$search%")
                        ->orWhere('tracking_code', 'like', "%$search%");
                });
            }
        });

        return $query;
    }

    private function updateProgress($progress)
    {
        // Update batch progress
        if ($this->batch) {
            $this->batch->updateProgress($progress);
        }
    }
}
