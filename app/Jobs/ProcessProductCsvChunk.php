<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Notifications\ProductImportFinished;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessProductCsvChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;

    protected $header;

    protected $adminId;

    protected $maxAttempts = 3;

    public function __construct(array $rows, array $header, $adminId)
    {
        $this->rows = $rows;
        $this->header = $header;
        $this->adminId = $adminId;
    }

    public function handle()
    {
        $successfulImports = 0;
        $failedImports = 0;
        $failedProductCodes = [];

        // Process rows in chunks for better memory management
        Collection::make($this->rows)
            ->chunk(100)
            ->each(function ($chunk) use (&$successfulImports, &$failedImports, &$failedProductCodes) {
                $productsData = $chunk->map(function ($row) use (&$failedProductCodes) {
                    $row = array_map('trim', $row);
                    $data = array_combine($this->header, $row);

                    if (Product::where('code', $data['code'])->orWhere('name', $data['name'])->exists()) {
                        $failedProductCodes[] = [
                            'name' => $data['name'],
                            'code' => $data['code'],
                        ];

                        return;
                    }

                    // Handle Category
                    $categoryId = null;
                    if (isset($data['category_name'])) {
                        $category = Category::firstOrCreate(['name' => $data['category_name']]);
                        $categoryId = $category->id;
                    }

                    // Handle Brand
                    $brandId = null;
                    if (isset($data['brand_name'])) {
                        $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
                        $brandId = $brand->id;
                    }

                    // Handle Unit
                    $unitId = null;
                    if (isset($data['unit_name'])) {
                        $unit = Unit::firstOrCreate(['name' => $data['unit_name']]);
                        $unitId = $unit->id;
                    }

                    return [
                        'created_by' => $this->adminId,
                        'name' => $data['name'],
                        'code' => $data['code'],
                        'brand_id' => $brandId ?? $data['brand_id'],
                        'category_id' => $categoryId ?? $data['category_id'],
                        'unit_id' => $unitId ?? $data['unit_id'],
                        'low_quantity_alert' => $data['low_quantity_alert'],
                        'description' => $data['description'],
                        'discount' => $data['discount'],
                        'discount_type' => $data['discount_type'],
                        'image' => $data['image_url'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                    ->filter()
                    ->toArray();

                // Bulk insert instead of individual creates
                if (count($productsData) > 0) {
                    Product::insert($productsData);
                    $successfulImports += count($productsData);
                } else {
                    // Track failed chunk size
                    $failedImports += count($chunk) - count($productsData);
                }
            });

        // Send notification to all admins with the 'admin' role or 'product_add' permission after processing is complete
        $this->sendProcessingFinishedNotification($successfulImports, $failedImports, $failedProductCodes);
    }

    protected function sendProcessingFinishedNotification($successfulImports, $failedImports, $failedProductCodes)
    {
        // Retrieve all admins with the 'admin' role or 'product_add' permission
        $admins = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->orWhereHas('permissions', function ($query) {
            $query->where('name', 'product_add');
        })->get();

        // Notify all admins
        foreach ($admins as $admin) {
            $admin->notify(new ProductImportFinished($successfulImports, $failedImports, $failedProductCodes));
        }
    }

    public function failed(\Throwable $exception)
    {
        // Log the failure
        Log::error('Product CSV chunk processing failed', [
            'exception' => $exception->getMessage(),
            'chunk_size' => count($this->rows),
        ]);
    }
}
