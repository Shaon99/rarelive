<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Services\CarrybeeIntegrationService;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $data['pageTitle'] = 'General Setting';
        $data['navGeneralSettingsActiveClass'] = 'active';
        $data['subNavGeneralSettingsActiveClass'] = 'active';
        $data['general'] = GeneralSetting::first();

        return view('backend.setting.general_setting')->with($data);
    }

    public function generalSettingUpdate(Request $request)
    {
        $general = GeneralSetting::first();
        $request->validate([
            'sitename' => 'required',
            'site_currency' => 'required|max:10',

            'logo' => [
                Rule::requiredIf(function () use ($general) {
                    return $general == null;
                }),
                'image',
                'mimes:jpg,png,jpeg,webp',
            ],
            'icon' => [
                Rule::requiredIf(function () use ($general) {
                    return $general == null;
                }),
                'image',
                'mimes:jpg,png,jpeg,webp',
            ],
            'invoice_logo' => [
                Rule::requiredIf(function () use ($general) {
                    return $general == null;
                }),
                'image',
                'mimes:jpg,png,jpeg,webp',
            ],
            'default_image' => [
                Rule::requiredIf(function () use ($general) {
                    return $general == null;
                }),
                'image',
                'mimes:jpg,png,jpeg,webp',
            ],
        ]);

        if ($request->has('logo')) {
            $size = '165x80';
            $logo = uploadImage($request->logo, filePath('logo'), $size, @$general->logo);
        }

        if ($request->has('default_image')) {
            $size = '300x300';
            $default_image = uploadImage($request->default_image, filePath('default'), $size, @$general->default_image);
        }

        if ($request->has('icon')) {
            $size = '80x80';
            $icon = uploadImage($request->icon, filePath('favicon'), $size, @$general->favicon);
        }

        if ($request->has('invoice_logo')) {
            $size = '165x80';
            $invoice_logo = uploadImage($request->invoice_logo, filePath('logo'), $size, @$general->invoice_logo);
        }

        GeneralSetting::updateOrCreate([
            'id' => 1,
        ], [
            'sitename' => $request->sitename,
            'pos_invoice_on_off' => $request->pos_invoice_on_off == 'on' ? 1 : 0,
            'fraud_check_on_off' => $request->fraud_check_on_off == 'on' ? 1 : 0,
            'pos_platform_on_off' => $request->pos_platform_on_off == 'on' ? 1 : 0,
            'pos_lead_on_off' => $request->pos_lead_on_off == 'on' ? 1 : 0,
            'site_currency' => $request->site_currency,
            'site_phone' => $request->phone,
            'site_address' => $request->address,
            'website' => $request->website,
            'invoice_header_note' => $request->invoice_header_note,
            'invoice_greeting' => $request->invoice_greeting,
            'logo' => isset($logo) ? ($logo ?? '') : GeneralSetting::first()->logo,
            'favicon' => isset($icon) ? ($icon ?? '') : GeneralSetting::first()->favicon,
            'invoice_logo' => isset($invoice_logo) ? ($invoice_logo ?? '') : GeneralSetting::first()->invoice_logo,
            'default_image' => isset($default_image) ? ($default_image ?? '') : GeneralSetting::first()->default_image,
        ]);
        $enabled = $request->has('queue_enabled') ? true : false;

        $this->setEnvValue([
            'QUEUE_ENABLED' => $enabled ? 'true' : 'false',
        ]);
        Cache::forget('general_setting');
        Artisan::call('config:clear');

        return back()->with('success', 'General setting has been updated.');
    }

    public function databaseBackup()
    {
        $mysqlHostName = env('DB_HOST');
        $mysqlUserName = env('DB_USERNAME');
        $mysqlPassword = env('DB_PASSWORD');
        $DbName = env('DB_DATABASE');

        $connect = new \PDO("mysql:host=$mysqlHostName;dbname=$DbName;charset=utf8", "$mysqlUserName", "$mysqlPassword", [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]);
        $get_all_table_query = 'SHOW TABLES';
        $statement = $connect->prepare($get_all_table_query);
        $statement->execute();
        $result = $statement->fetchAll();

        $output = '';
        foreach ($result as $table) {

            $show_table_query = 'SHOW CREATE TABLE ' . $table[0] . '';
            $statement = $connect->prepare($show_table_query);
            $statement->execute();
            $show_table_result = $statement->fetchAll();

            foreach ($show_table_result as $show_table_row) {
                $output .= "\n\n" . $show_table_row['Create Table'] . ";\n\n";
            }
            $select_query = 'SELECT * FROM ' . $table[0] . '';
            $statement = $connect->prepare($select_query);
            $statement->execute();
            $total_row = $statement->rowCount();

            for ($count = 0; $count < $total_row; $count++) {
                $single_result = $statement->fetch(\PDO::FETCH_ASSOC);

                $table_column_array = array_keys($single_result);
                $table_value_array = array_values($single_result);
                $output .= "\nINSERT INTO $table[0] (";
                $output .= '' . implode(', ', $table_column_array) . ') VALUES (';
                $output .= "'" . implode("','", $table_value_array) . "');\n";
            }
        }
        $file_name = 'database_backup_on_' . date('y-m-d') . '.sql';
        $file_handle = fopen($file_name, 'w+');
        fwrite($file_handle, $output);
        fclose($file_handle);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_name));
        ob_clean();
        flush();
        readfile($file_name);
        unlink($file_name);
    }

    public function cacheClear()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');

        return back()->with('success', 'Caches cleared successfully!');
    }

    public function integration()
    {
        $data['pageTitle'] = 'Integration Settings';
        $data['navGeneralSettingsActiveClass'] = 'active';
        $data['subNavIntegrationGeneralSettingsActiveClass'] = 'active';

        $data['setting'] = GeneralSetting::select(
            'id',
            'enable_online_deliver',
            'enable_carrybee',
            'carrybee_accounts',
            'carrybee_webhook_secret',
            'carrybee_webhook_integration_id',
            'steadfast_api_key',
            'steadfast_api_secret',
            'steadfast_cod_charge',
            'cloudinary_enable'
        )->first();

        $data['carrybeeAccountsForm'] = $data['setting']->carrybee_accounts ?? [];
        $data['carrybeeConfigured'] = count(CarrybeeIntegrationService::accounts()) > 0;

        $data['masked_key'] = $data['setting']->steadfast_api_key;
        $data['masked_secret'] = $data['setting']->steadfast_api_secret;
        $data['webhook_secret'] = $data['setting']->steadfast_webhook_secret;

        $data['cod_charge'] = $data['setting']->steadfast_cod_charge;

        return view('backend.setting.integration')->with($data);
    }

    public function integrationUpdate(Request $request)
    {
        $formType = $request->input('form_type');
        $general = GeneralSetting::firstOrFail();

        if ($formType === 'cloudinary') {
            $validated = $request->validate([
                'cloudinary_api_key' => 'required|string',
                'cloudinary_api_secret' => 'required|string',
                'cloudinary_cloud_name' => 'required|string',
                'cloudinary_enable' => 'nullable|boolean',
            ]);

            $general->update([
                'cloudinary_enable' => $request->has('cloudinary_enable') ? 1 : 0,
            ]);

            $this->setEnvValue([
                'CLOUDINARY_API_KEY' => $validated['cloudinary_api_key'],
                'CLOUDINARY_API_SECRET' => $validated['cloudinary_api_secret'],
                'CLOUDINARY_CLOUD_NAME' => $validated['cloudinary_cloud_name'],
                'CLOUDINARY_URL' => sprintf(
                    'cloudinary://%s:%s@%s',
                    $validated['cloudinary_api_key'],
                    $validated['cloudinary_api_secret'],
                    $validated['cloudinary_cloud_name']
                ),
            ]);
        }

        if ($formType === 'steadfast') {
            $validated = $request->validate([
                'steadfast_api_key' => 'required|string',
                'steadfast_api_secret' => 'required|string',
                'steadfast_cod_charge' => 'nullable|string',
                'enable_online_deliver' => 'nullable|boolean',
            ]);

            $general->update([
                'steadfast_api_key' => trim($validated['steadfast_api_key']),
                'steadfast_api_secret' => trim($validated['steadfast_api_secret']),
                'steadfast_cod_charge' => trim($validated['steadfast_cod_charge']) ?? 1,
                'enable_online_deliver' => $request->has('enable_online_deliver') ? 1 : 0,
            ]);

            if ($request->filled('steadfast_webhook_secret')) {
                $this->setEnvValue(['STEADFAST_WEBHOOK_SECRET' => $request->steadfast_webhook_secret]);
            }

            if ($request->filled('steadfast_base_url')) {
                $this->setEnvValue(['STEADFAST_BASE_URL' => $request->steadfast_base_url]);
            }
        }

        if ($formType === 'carrybee') {
            $merged = $this->mergeCarrybeeAccountsFromRequest($request, $general);
            if ($merged instanceof \Illuminate\Http\RedirectResponse) {
                return $merged;
            }

            $enable = $request->boolean('enable_carrybee') && count($merged) > 0 ? 1 : 0;

            $request->validate([
                'carrybee_webhook_secret' => 'nullable|string|max:128',
                'carrybee_webhook_integration_id' => 'nullable|string|max:80',
            ]);

            $postedIntegration = trim((string) $request->input('carrybee_webhook_integration_id', ''));
            $integrationId = $postedIntegration !== ''
                ? $postedIntegration
                : (string) ($general->carrybee_webhook_integration_id ?? '');

            $postedSecret = trim((string) $request->input('carrybee_webhook_secret', ''));
            $webhookSecret = $postedSecret !== '' ? $postedSecret : (string) ($general->carrybee_webhook_secret ?? '');
            if ($webhookSecret === '' && $integrationId === '') {
                $webhookSecret = Str::random(64);
            }

            $general->update([
                'carrybee_accounts' => $merged,
                'enable_carrybee' => $enable,
                'carrybee_webhook_secret' => $webhookSecret,
                'carrybee_webhook_integration_id' => $integrationId !== '' ? $integrationId : null,
            ]);
        }

        Artisan::call('config:clear');
        Cache::forget('general_setting');
        Cache::forget('general_setting_courier');

        return back()->with('success', ucfirst($formType) . ' integration updated successfully.');
    }

    /**
     * @return array<int, array<string, string>>|\Illuminate\Http\RedirectResponse
     */
    protected function mergeCarrybeeAccountsFromRequest(Request $request, GeneralSetting $general)
    {
        $existingById = collect($general->carrybee_accounts ?? [])->keyBy('id');
        $inputRows = $request->input('carrybee_accounts', []);

        if (! is_array($inputRows)) {
            $inputRows = [];
        }

        $merged = [];
        $errors = [];

        foreach ($inputRows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowNum = is_numeric($i) ? ((int) $i + 1) : 1;

            $apiUrl = trim((string) ($row['api_url'] ?? ''));
            $clientId = trim((string) ($row['client_id'] ?? ''));
            $clientContext = trim((string) ($row['client_context'] ?? ''));
            $accountName = trim((string) ($row['account_name'] ?? $row['label'] ?? ''));

            if ($apiUrl === '' && $clientId === '' && $clientContext === '' && $accountName === '') {
                continue;
            }

            if ($accountName === '') {
                $errors["carrybee_accounts.{$i}.account_name"] = "Row {$rowNum}: enter an account name so you can recognize this Carrybee account.";

                continue;
            }

            if ($apiUrl === '' || $clientId === '' || $clientContext === '') {
                $errors["carrybee_accounts.{$i}._row"] = "Row {$rowNum}: API URL, client ID, and client context are required.";

                continue;
            }

            $label = $accountName;
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '') {
                $id = (string) Str::uuid();
            }

            $prev = $existingById->get($id);
            $secretIn = trim((string) ($row['client_secret'] ?? ''));
            $finalSecret = $secretIn;
            if ($finalSecret === '' && $prev) {
                $finalSecret = (string) ($prev['client_secret'] ?? '');
            }
            if ($finalSecret === '') {
                $errors["carrybee_accounts.{$i}.client_secret"] = 'Enter the client secret for new accounts, or leave it blank to keep the saved secret when editing.';

                continue;
            }

            $storeId = trim((string) ($row['store_id'] ?? ''));

            $merged[] = [
                'id' => $id,
                'account_name' => $label,
                'label' => $label,
                'api_url' => rtrim($apiUrl, '/'),
                'client_id' => $clientId,
                'client_secret' => $finalSecret,
                'client_context' => $clientContext,
                'store_id' => $storeId,
            ];
        }

        if (! empty($errors)) {
            $safeInput = $request->all();
            if (isset($safeInput['carrybee_accounts']) && is_array($safeInput['carrybee_accounts'])) {
                foreach ($safeInput['carrybee_accounts'] as $k => $acc) {
                    if (is_array($acc)) {
                        unset($safeInput['carrybee_accounts'][$k]['client_secret']);
                    }
                }
            }

            return back()->withErrors($errors)->withInput($safeInput);
        }

        return $merged;
    }

    protected function setEnvValue(array $values)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $escaped = preg_quote("{$key}=", '/');
            $pattern = "/^{$escaped}.*$/m";
            $line = "{$key}=\"{$value}\"";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $line, $envContent);
            } else {
                $envContent .= "\n{$line}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    public function imageGallery(Request $request)
    {
        try {
            // Cache key based on cursor parameter
            $cacheKey = 'cloudinary_images_' . ($request->input('next_cursor') ?? 'initial');

            // Try to get from cache first
            return Cache::remember($cacheKey, now()->addHours(24), function () use ($request) {
                $adminApi = new AdminApi();

                $options = [
                    'resource_type' => 'image',
                    'max_results' => 20,
                    'prefix' => 'rareliv/',
                    'type' => 'upload'
                ];

                if ($request->has('next_cursor')) {
                    $options['next_cursor'] = $request->input('next_cursor');
                }

                $result = $adminApi->assets($options);

                $images = collect($result['resources'])->map(function ($image) {
                    $optimizedUrl = preg_replace(
                        '~(upload/)~',
                        'upload/f_auto,q_auto/',
                        $image['url'],
                        1
                    );

                    return $optimizedUrl;
                })->toArray();

                return response()->json([
                    'images' => $images,
                    'next_cursor' => $result['next_cursor'] ?? null,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch images from Cloudinary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadImage(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:2048',
        ]);
        $image = $request->file('image');

        try {
            $cloudinary = new Cloudinary();
            $uploadResult = $cloudinary->uploadApi()->upload($image->getRealPath(), [
                'folder' => 'rareliv/',
                'overwrite' => true,
            ]);

            $publicId = $uploadResult['public_id'];

            $isSvg = strtolower($image->getClientOriginalExtension()) === 'svg';

            $finalUrl = $cloudinary->image($publicId . '.' . ($isSvg ? 'svg' : 'webp'))
                ->delivery(\Cloudinary\Transformation\Delivery::quality('auto'))
                ->format('auto')
                ->toUrl();

            $cacheKey = 'cloudinary_images_' . ($request->input('next_cursor') ?? 'initial');
            Cache::forget($cacheKey);

            return response()->json([
                'url' => $finalUrl,
                'message' => 'Image uploaded and cache invalidated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload image to Cloudinary.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'image_urls' => 'required|array',
            'image_urls.*' => 'url',
        ]);

        $cloudinary = new Cloudinary();
        $uploadApi = $cloudinary->uploadApi();
        $failed = [];
        $successCount = 0;

        try {
            foreach ($request->image_urls as $url) {
                $publicId = $this->getPublicIdFromUrl($url);
                if (! $publicId) {
                    $failed[] = ['url' => $url, 'reason' => 'Invalid public ID'];

                    continue;
                }

                try {
                    $uploadApi->destroy($publicId);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Cloudinary delete failed for {$publicId}: " . $e->getMessage());
                    $failed[] = [
                        'url' => $url,
                        'reason' => $e->getMessage(),
                    ];
                }
            }

            $cacheKey = 'cloudinary_images_' . ($request->input('next_cursor') ?? 'initial');
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'deleted_count' => $successCount,
                'failed_count' => count($failed),
                'failed_items' => $failed,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk delete operation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Bulk delete operation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getPublicIdFromUrl($url)
    {
        try {
            $parsed = parse_url($url);
            if (empty($parsed['path'])) {
                return;
            }

            $path = $parsed['path'];
            $uploadPos = strpos($path, '/upload/');

            if ($uploadPos === false) {
                return;
            }

            $pathAfterUpload = substr($path, $uploadPos + strlen('/rareliv/'));
            $parts = array_filter(explode('/', $pathAfterUpload));

            // Remove transformation parameters and version
            $parts = array_values(array_filter($parts, function ($part) {
                return ! str_contains($part, ',')
                    && ! str_contains($part, 'auto')
                    && ! preg_match('/^v\d+$/', $part);
            }));

            if (empty($parts)) {
                return;
            }

            $final = implode('/', $parts);

            return pathinfo($final, PATHINFO_DIRNAME) . '/' . pathinfo($final, PATHINFO_FILENAME);
        } catch (\Exception $e) {
            Log::error('Failed to parse Cloudinary URL: ' . $e->getMessage());

            return;
        }
    }
}
