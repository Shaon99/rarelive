<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Transformers\BaseTransformer;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateExpenseReportCsvJob implements ShouldQueue
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
        $query = Expense::with('expenseCategory');

        // Apply filters directly in the query
        $this->applyFilters($query, $this->filters);

        // Total sales count for progress tracking
        $totalExpense = $query->count(); // Apply filters before counting
        $processedExpense = 0;

        // Process data in chunks
        $query->chunk(100, function ($expense) use ($file, $totalExpense, &$processedExpense) {
            foreach ($expense as $expenseItem) {
                // Transform each sale using the SalesReportTransformer
                $row = $this->transformer->transform($expenseItem);
                fputcsv($file, $row); // Write each transformed row to the file

                $processedExpense++;
                $progress = (int) (($processedExpense / $totalExpense) * 100); // Calculate progress percentage
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
        // Apply filters on expense
        if (isset($filters['category'])) {
            $query->whereIn('expense_category_id', $filters['category']);
        }

        // Filter by date_range (created_at)
        if (isset($filters['date_range'])) {
            [$startDate, $endDate] = explode(' - ', $filters['date_range']);
            $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        // Apply search filter on amounts
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('amount', 'like', "%$search%");
        }

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
