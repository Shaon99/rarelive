<?php

namespace App\Jobs;

use App\Transformers\BaseTransformer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportCsvJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 3600;

    protected $chunk;

    protected $model;

    protected $fileName;

    protected $transformer;

    /**
     * Undocumented function
     *
     * @param [type] $model
     */
    public function __construct($chunk, string $fileName, BaseTransformer $transformer)
    {
        $this->chunk = $chunk;
        $this->fileName = $fileName;
        $this->transformer = $transformer;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $filePath = storage_path('app/public/'.$this->fileName);

        $isNewFile = ! file_exists($filePath);
        $file = fopen($filePath, 'a');

        if ($isNewFile) {
            // Retrieve headers from the transformer
            $headers = $this->transformer->getHeaders();
            fputcsv($file, $headers);
        }

        foreach ($this->chunk as $item) {
            // Transform the item using the transformer
            $row = $this->transformer->transform($item);
            fputcsv($file, $row);
        }

        fclose($file);
    }
}
