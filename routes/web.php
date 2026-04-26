<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DownloadManager;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PruneOldLogsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchasesController;
use App\Http\Controllers\Admin\RecycleBinController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitsController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('admin.login'));

Route::name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'loginPage'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::prefix('password')->group(function () {
        Route::get('reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.reset');
        Route::post('reset', [ForgotPasswordController::class, 'sendResetCodeEmail']);
        Route::post('verify-code', [ForgotPasswordController::class, 'verifyCode'])->name('password.verify.code');
        Route::get('reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.form');
        Route::post('reset/change', [ResetPasswordController::class, 'reset'])->name('password.change');
    });

    Route::middleware('admin')->group(function () {
        Route::get('dashboard', [HomeController::class, 'dashboard'])->name('home');
        Route::get('filter', [HomeController::class, 'filter'])->name('filter');
        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('profile', [AdminController::class, 'profileUpdate'])->name('profile.update');
        Route::post('change/password', [AdminController::class, 'changePassword'])->name('change.password');

        // Role Permission
        Route::resource('roles', RolePermissionController::class);
        Route::get('roles/get-sub-module/{id}', [RolePermissionController::class, 'getsubmodule'])->name('getsubmodule');
        Route::get('permissions', [RolePermissionController::class, 'permission'])->name('permission');
        Route::post('permissions-add', [RolePermissionController::class, 'permissionPost'])->name('permissionPost');
        Route::post('permissions-update/{id}', [RolePermissionController::class, 'permissionUpdate'])->name('permissionUpdate');
        Route::delete('permissions-delete/{id}', [RolePermissionController::class, 'destroyPermission'])->name('destroyPermission');

        // Admin User
        Route::prefix('admin/user')->group(function () {
            Route::get('list', [AdminController::class, 'index'])->name('index');
            Route::get('create', [AdminController::class, 'create'])->name('create');
            Route::post('store', [AdminController::class, 'store'])->name('store');
            Route::get('edit/{id}', [AdminController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AdminController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [AdminController::class, 'destroy'])->name('destroy');
        });

        // customer
        Route::resource('customer', CustomerController::class);
        Route::get('customer-recover/{id}', [CustomerController::class, 'customerRestore'])->name('customer.restore');
        Route::delete('customer-delete-forever/{id}', [CustomerController::class, 'customerDelete'])->name('customer.deleteforever');
        Route::post('/search-customer', [CustomerController::class, 'searchCustomer'])->name('search.customer');
        Route::post('/add-address', [CustomerController::class, 'addAddress'])->name('add.address');

        Route::get('order-history/{id}', [CustomerController::class, 'customerOrderHistory'])->name('customer.customerOrderHistory');
        Route::get('customer-ledger/{id}', [CustomerController::class, 'customerLedger'])->name('customer.customerLedger');
        // unit
        Route::resource('unit', UnitsController::class);

        // category
        Route::resource('category', CategoryController::class);

        // brand
        Route::resource('brand', BrandController::class);

        // product attribute
        Route::get('product-attribute', [BrandController::class, 'productAttribute'])->name('productAttribute');
        Route::post('product-attribute-store', [BrandController::class, 'productAttributeStore'])->name('productAttributeStore');
        Route::post('product-attribute-update/{id}', [BrandController::class, 'productAttributeUpdate'])->name('productAttributeUpdate');
        Route::delete('product-attribute-delete/{id}', [BrandController::class, 'productAttributeDelete'])->name('productAttributeDelete');

        // warehouse
        Route::resource('warehouse', WarehouseController::class);
        // supplier
        Route::resource('supplier', SupplierController::class);
        Route::get('product-purchases/{id}', [SupplierController::class, 'supplierPurchases'])->name('supplierPurchases');

        // product
        Route::resource('product', ProductController::class);
        Route::get('product-recover/{id}', [ProductController::class, 'productRestore'])->name('product.restore');
        Route::delete('product-type-delete-forever/{id}', [ProductController::class, 'productDelete'])->name('product.deleteforever');
        Route::get('product-transfer', [ProductController::class, 'productTransfer'])->name('product.transfer');
        Route::post('product-transfer-post', [ProductController::class, 'productTransferPost'])->name('transfer.post');
        Route::get('product-transfer/{id}', [ProductController::class, 'productTransferRestore'])->name('productTransfer.restore');
        Route::delete('product-transfer-delete-forever/{id}', [ProductController::class, 'productTransferDelete'])->name('productTransfer.deleteforever');
        Route::delete('product-transfer-delete/{id}', [ProductController::class, 'PTDelete'])->name('productTransfer.destroy');

        Route::post('product/import', [ProductController::class, 'import'])->name('product.import');

        Route::get('/warehouses/{productId}', [ProductController::class, 'getWarehousesByProduct'])->name('getWarehousesByProduct');

        Route::get('combo-product', [ProductController::class, 'comboProduct'])->name('comboProduct.index');
        Route::get('combo-product-create', [ProductController::class, 'comboProductCreate'])->name('comboProduct.create');
        Route::post('combo-product-store', [ProductController::class, 'storeCombo'])->name('comboProduct.store');
        Route::get('combo-product-view/{id}', [ProductController::class, 'viewCombo'])->name('comboProduct.view');
        Route::get('combo-product-edit/{id}', [ProductController::class, 'editCombo'])->name('comboProduct.edit');
        Route::post('combo-product-update/{id}', [ProductController::class, 'updateCombo'])->name('comboProduct.update');
        Route::delete('combo-product-delete/{id}', [ProductController::class, 'deleteCombo'])->name('comboProduct.delete');
        Route::get('combo-product-recover/{id}', [ProductController::class, 'comboRestore'])->name('comboProduct.restore');
        Route::delete('combo-product-delete-forever/{id}', [ProductController::class, 'comboProductDelete'])->name('comboProduct.deleteforever');

        // Purchase
        Route::resource('purchases', PurchasesController::class);
        Route::post('purchases-due-payment/{id}', [PurchasesController::class, 'purchasesDuePayment'])->name('purchases.duepayment');
        Route::get('purchases-recover/{id}', [PurchasesController::class, 'purchasesRestore'])->name('purchases.restore');
        Route::delete('purchases-type-delete-forever/{id}', [PurchasesController::class, 'purchasesDelete'])->name('purchases.deleteforever');
        Route::get('check-stock', [PurchasesController::class, 'checkStock'])->name('stock');
        Route::get('purchases-status/{id}', [PurchasesController::class, 'statusChange'])->name('statusChange');
        Route::get('/purchases-ledger', [PurchasesController::class, 'purchasesLedger'])->name('purchases.ledger');

        Route::post('quantity-transfer/{id}', [PurchasesController::class, 'transfer'])->name('quantity.transfer');
        Route::get('stock-history/{id}', [PurchasesController::class, 'stockHistory'])->name('stockhistory');

        Route::get('/purchases/{purchaseId}/return', [PurchasesController::class, 'purchasesReturn'])->name('purchase_returns.create');
        Route::post('/purchases/{purchaseId}/return', [PurchasesController::class, 'purchaseReturnStore'])->name('purchase_returns.store');

        Route::resource('sales', SalesController::class);
        Route::post('sales-due-payment/{id}', [SalesController::class, 'salesDuePayment'])->name('sales.duepayment');
        Route::get('sales-recover/{id}', [SalesController::class, 'salesRestore'])->name('sales.restore');
        Route::delete('sales-type-delete-forever/{id}', [SalesController::class, 'salesDelete'])->name('sales.deleteforever');
        Route::get('category-product/{id}', [SalesController::class, 'categoryProduct'])->name('category.product');
        Route::get('all-product', [SalesController::class, 'allProduct'])->name('all.product');
        Route::get('sales-invoice/{id}', [SalesController::class, 'invoice'])->name('invoice');
        Route::get('sales-level-print/{id}', [SalesController::class, 'levelPrint'])->name('sales.levelPrint');
        Route::get('sales-level-print-bulk', [SalesController::class, 'levelPrintBulk'])->name('sales.levelPrintBulk');
        Route::get('search', [SalesController::class, 'searchProduct'])->name('search.product');
        Route::get('/sales-ledger', [SalesController::class, 'salesLedger'])->name('sales.ledger');
        Route::get('/sales-status-change/{id}', [SalesController::class, 'changeStatus'])->name('sales.changeStatus');
        Route::get('/money-flow', [SalesController::class, 'cashFlow'])->name('cash.flow');
        Route::get('/sale-return-adjustment/{id}', [StockController::class, 'saleReturnAdjustment'])->name('saleReturnAdjustment');
        Route::post('/sale-return-adjustment-store', [StockController::class, 'store'])->name('saleReturnAdjustment.store');
        Route::get('/sale-return-list', [StockController::class, 'returnList'])->name('returnList.index');
        Route::get('/general-ledger', [ReportController::class, 'generalLedger'])->name('general.ledger');
        Route::post('/sales/delete', [SalesController::class, 'deleteBulk'])->name('sales.deleteBulk');
        Route::post('/send-to-steadfast', [SalesController::class, 'sendToSteadFast'])->name('sales.sendToSteadFast');
        Route::post('/send-to-carrybee', [SalesController::class, 'sendToCarrybee'])->name('sales.sendToCarrybee');
        Route::post('/single-order-send-to-steadfast', [SalesController::class, 'sendToSteadFastSingleOrder'])->name('sales.sendToSteadFastSingleOrder');

        Route::post('/check-steadfast-status', [SalesController::class, 'checkSteadfastStatus'])->name('sales.checkSteadfastStatus');

        Route::get('/recent-sales', [SalesController::class, 'recentSales'])->name('recentSales');

        Route::get('/get-thana', [CustomerController::class, 'getThana']);

        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.markAllAsRead');

        // Route to edit the address
        Route::put('/admin/address/{id}', [CustomerController::class, 'updateAddress'])->name('address.edit');
        // Route to delete the address
        Route::delete('address/{id}', [CustomerController::class, 'destroyAddress'])->name('address.destroy');

        // expense-category
        Route::resource('expense-category', ExpenseCategoryController::class);
        Route::get('expense-category-/{id}', [ExpenseCategoryController::class, 'expensecategoryRestore'])->name('expensecategory.restore');
        Route::delete('expense-category-delete-forever/{id}', [ExpenseCategoryController::class, 'expensecategoryDelete'])->name('expensecategory.deleteforever');
        // expense
        Route::resource('expense', ExpenseController::class);
        Route::get('expense-restore/{id}', [ExpenseController::class, 'expenseRestore'])->name('expense.restore');
        Route::delete('expense-delete-forever/{id}', [ExpenseController::class, 'expenseDelete'])->name('expense.deleteforever');
        Route::get('/expense-ledger', [ExpenseController::class, 'expenseLedger'])->name('expense.ledger');
        Route::get('gallery-image', [GeneralSettingController::class, 'imageGallery'])->name('gallery.images');
        Route::post('gallery-image-upload', [GeneralSettingController::class, 'uploadImage'])->name('gallery.images.upload');

        Route::post('gallery-image-delete-bulk', [GeneralSettingController::class, 'bulkDelete'])->name('gallery.bulk.delete');

        Route::get('/invoices/print', [ReportController::class, 'printMultiple']);

        // General Settings
        Route::get('general/setting', [GeneralSettingController::class, 'index'])->name('general.setting');
        Route::get('general/integrations', [GeneralSettingController::class, 'integration'])->name('general.integrationSetting');
        Route::post('general/integration-setting-update', [GeneralSettingController::class, 'integrationUpdate'])->name('general.integrationSettingUpdate');

        Route::post('general/setting', [GeneralSettingController::class, 'generalSettingUpdate']);

        Route::get('database', [GeneralSettingController::class, 'databaseBackup'])->name('general.database');

        Route::get('cacheclear', [GeneralSettingController::class, 'cacheClear'])->name('general.cacheclear');

        // Email Configuration
        Route::get('email/config', [EmailTemplateController::class, 'emailConfig'])->name('email.config');
        Route::post('email/config', [EmailTemplateController::class, 'emailConfigUpdate']);

        Route::get('email/templates', [EmailTemplateController::class, 'emailTemplates'])->name('email.templates');

        Route::get('email/templates/{template}', [EmailTemplateController::class, 'emailTemplatesEdit'])->name('email.templates.edit');
        Route::post('email/templates/{template}', [EmailTemplateController::class, 'emailTemplatesUpdate']);

        // Manage Language
        Route::get('language', [LanguageController::class, 'index'])->name('language.index');
        Route::post('language', [LanguageController::class, 'store']);
        Route::post('language/edit/{id}', [LanguageController::class, 'update'])->name('language.edit');
        Route::delete('language/delete/{id}', [LanguageController::class, 'delete'])->name('language.delete');
        Route::get('language/translator/{lang}', [LanguageController::class, 'translate'])->name('language.translator');
        Route::post('language/translator/{lang}', [LanguageController::class, 'translateUpdate']);
        Route::get('language/import', [LanguageController::class, 'import'])->name('language.import');

        Route::get('changeLang', [LanguageController::class, 'changeLang'])->name('changeLang');

        Route::post('/maintenance/prune-old-logs', PruneOldLogsController::class)->name('maintenance.prune-old-logs');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/mark-all-as-read', [NotificationController::class, 'markNotification'])->name('markNotification');
        Route::get('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.log');
        Route::get('/activity-log/{id}', [ActivityLogController::class, 'show'])->name('activityLog.show');

        Route::get('recycle-bin', [RecycleBinController::class, 'index'])->name('recycle_bin.index');
        Route::delete('recycle-bin/delete-forever/{id}', [RecycleBinController::class, 'deleteForever'])->name('recycle_bin.delete_forever');
        Route::post('/recycle-bin-multiple-delete-forever', [RecycleBinController::class, 'bulkAction'])->name('recycle_bin.multiple.delete_forever');
        Route::post('/recycle-bin/{id}', [RecycleBinController::class, 'restore'])->name('recycle_bin.restore');

        Route::get('profit/loss/report', [ReportController::class, 'profitLossReport'])->name('report.profitLossReport');

        Route::get('Sales-report', [ReportController::class, 'salesReport'])->name('sales.report');
        Route::get('/download-sales-report', [ReportController::class, 'downloadCsv'])->name('download.sales.report');
        Route::get('/download-manager', [DownloadManager::class, 'index'])->name('download.manager.index');

        Route::get('expense-report', [ReportController::class, 'expenseReport'])->name('expense.report');
        Route::get('/download-expense-report', [ReportController::class, 'downloadExpenseCsv'])->name('download.expense.report');

        Route::delete('/download-manager/{file}', [DownloadManager::class, 'delete'])->name('download.manager.delete');
        Route::post('/admin/download-manager/delete-multiple', [DownloadManager::class, 'deleteMultiple'])->name('download.manager.delete.multiple');
        Route::get('Purchases-report', [ReportController::class, 'purchasesReport'])->name('purchases.report');
        Route::get('daily-report', [ReportController::class, 'dailyReport'])->name('daily.report');

        Route::get('stock-report', [ReportController::class, 'stockReport'])->name('stock.report');

        Route::get('manage-account', [ReportController::class, 'manageAccount'])->name('manageAccount');

        Route::resource('accounts', PaymentMethodController::class);

        Route::get('get-steadfast-current-balance', [PaymentMethodController::class, 'getSteadfastCurrentBalance'])->name('steadfast.current.balance');

        Route::post('update-owner-capital', [PaymentMethodController::class, 'updateOwnerCapital'])->name('update.ownerCapital');

        Route::post('transfer-funds', [PaymentMethodController::class, 'transferFunds'])->name('transferFunds');
        Route::post('add-fund', [PaymentMethodController::class, 'addFund'])->name('addFund');
        Route::post('withdraw-fund', [PaymentMethodController::class, 'withdrawFund'])->name('withdrawFund');

        Route::get('fraud-check', [PaymentMethodController::class, 'fraudCheck'])->name('fraudCheck');

        Route::post('/delete-cloudinary-image', [StockController::class, 'deleteCloudinaryImage']);

        Route::get('/stock-adjustment', [StockController::class, 'adjustmentForm'])->name('stock.adjust.form');
        Route::post('/stock-adjustment', [StockController::class, 'addStock'])->name('stock.adjust');

        Route::get('employees', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('employee-create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('employee-store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::get('employee-edit/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::put('employee-update/{id}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::delete('employee-delete/{id}', [EmployeeController::class, 'destroy'])->name('employee.delete');

        Route::get('salaries', [SalaryController::class, 'index'])->name('salary.index');
        Route::get('salaries-create', [SalaryController::class, 'create'])->name('salaries.create');
        Route::post('salaries-store', [SalaryController::class, 'store'])->name('salaries.store');
        Route::get('salaries-edit/{id}', [SalaryController::class, 'edit'])->name('salaries.edit');
        Route::put('salaries-update/{id}', [SalaryController::class, 'update'])->name('salaries.update');
        Route::delete('salaries-delete/{id}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
        Route::get('salary-show/{id}', [SalaryController::class, 'show'])->name('salary.show');
        Route::post('salary-payments/{id}', [SalaryController::class, 'salaryPayment'])->name('salary.salaryPayment');

        Route::get('/export/download/{fileName}', function ($file) {
            $path = storage_path('app/public/' . $file);
            if (file_exists($path)) {
                $response = response()->download($path);

                return $response;
            }

            session()->flash('error', 'File not found, please try again.');

            return redirect()->back();
        })->name('export.download');

        Route::get('/admin/batch/{batchId}/progress', function ($batchId) {
            $batch = Bus::findBatch($batchId);
            if ($batch) {
                return response()->json([
                    'progress' => $batch->progress(),
                ]);
            }

            return response()->json(['progress' => 0], 404);
        });

        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    });
});

Route::post('/steadfast-webhook', [SalesController::class, 'handleSteadFastWebhook'])
    ->middleware('auth.webhook')
    ->name('steadfastWebhook');

Route::match(['GET', 'POST', 'HEAD'], '/carrybee-webhook', [SalesController::class, 'handleCarrybeeWebhook'])
    ->middleware('carrybee.webhook')
    ->name('carrybeeWebhook');

Route::post('/generate-description-ai', [ProductController::class, 'generateDescriptionAI']);
