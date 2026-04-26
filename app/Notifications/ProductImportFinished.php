<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ProductImportFinished extends Notification
{
    protected $successfulImports;

    protected $failedImports;

    protected $failedProductCodes;

    public function __construct($successfulImports, $failedImports, $failedProductCodes)
    {
        $this->successfulImports = $successfulImports;
        $this->failedImports = $failedImports;
        $this->failedProductCodes = $failedProductCodes;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'successful_imports' => $this->successfulImports,
            'failed_imports' => $this->failedImports,
            'failed_product_codes' => $this->failedProductCodes,
            'message' => 'Product import has been completed.',
        ];
    }
}
