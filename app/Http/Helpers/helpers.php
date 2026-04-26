<?php

use App\Constants\CommonConstant;
use App\Models\EmailTemplate;
use App\Models\GeneralSetting;
use App\Models\Transaction;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function makeDirectory($path)
{
    if (file_exists($path)) {
        return true;
    }

    return mkdir($path, 0755, true);
}

function removeFile($path)
{
    return file_exists($path) && is_file($path) ? @unlink($path) : false;
}

function uploadImage($file, $location, $size = null, $old = null)
{
    $path = makeDirectory($location);

    if (! $path) {
        throw new Exception('File could not be created.');
    }

    if (! empty($old)) {
        removeFile($location.'/'.$old);
    }

    $filename = uniqid().time().'.webp';

    $image = Image::make($file);

    $quality = 95;

    if (! empty($size)) {
        $size = explode('x', strtolower($size));

        $canvas = Image::canvas(600, 600);

        $image = $image->resize(600, 600, function ($constraint) {
            $constraint->aspectRatio();
        });

        $canvas->insert($image, 'center');
        $canvas->encode('webp', $quality);  // Set the quality for the WebP image
        $canvas->save($location.'/'.$filename);
    } else {
        $image->encode('webp', $quality);  // Set the quality for the WebP image
        $image->save($location.'/'.$filename);
    }

    return $filename;
}

function verificationCode($length)
{
    if ($length == 0) {
        return 0;
    }
    $min = pow(10, $length - 1);
    $max = 0;
    while ($length > 0 && $length--) {
        $max = ($max * 10) + 9;
    }

    return random_int($min, $max);
}

function filePath($folder_name)
{
    return 'assets/images/'.$folder_name;
}

function frontendFormatter($key)
{
    return ucwords(str_replace('_', ' ', $key));
}

function getFile($folder_name, $filename)
{
    return asset('assets/images/'.$folder_name.'/'.$filename);
}

function variableReplacer($code, $value, $template)
{
    return str_replace($code, $value, $template);
}

function sendGeneralMail($data)
{
    $general = GeneralSetting::first();

    if ($general->email_method == 'php') {
        $headers = "From: $general->sitename <$general->site_email> \r\n";
        $headers .= "Reply-To: $general->sitename <$general->site_email> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        @mail($data['email'], $data['subject'], $data['message'], $headers);
    } else {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $general->email_config->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $general->email_config->smtp_username;
            $mail->Password = $general->email_config->smtp_password;
            if ($general->email_config->smtp_encryption == 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port = $general->email_config->smtp_port;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($general->site_email, $general->sitename);
            $mail->addAddress($data['email'], $data['name']);
            $mail->addReplyTo($general->site_email, $general->sitename);
            $mail->isHTML(true);
            $mail->Subject = $data['subject'];
            $mail->Body = $data['message'];
            $mail->send();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}

function sendMail($key, array $data, $user)
{
    $general = GeneralSetting::first();
    $template = EmailTemplate::where('name', $key)->first();
    $message = variableReplacer('{username}', $user->username, $template->template);
    $message = variableReplacer('{sent_from}', @$general->sitename, $message);

    foreach ($data as $key => $value) {
        $message = variableReplacer('{'.$key.'}', $value, $message);
    }

    if ($general->email_method == 'php') {
        $headers = "From: $general->sitename <$general->site_email> \r\n";
        $headers .= "Reply-To: $general->sitename <$general->site_email> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        @mail($user->email, $template->subject, $message, $headers);
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $general->email_config->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $general->email_config->smtp_username;
            $mail->Password = $general->email_config->smtp_password;
            if ($general->email_config->smtp_encryption == 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port = $general->email_config->smtp_port;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($general->site_email, $general->sitename);
            $mail->addAddress($user->email, $user->username);
            $mail->addReplyTo($general->site_email, $general->sitename);
            $mail->isHTML(true);
            $mail->Subject = $template->subject;
            $mail->Body = $message;
            $mail->send();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}

function imageUploadWithoutCrop($file, $folderName, $oldFile)
{
    if ($oldFile) {
        @unlink(filePath($folderName).'/'.$oldFile);
    }
    $fileName = uniqid().'.'.$file->getClientOriginalExtension();
    $file->move(filePath($folderName), $fileName);

    return $fileName;
}

if (! function_exists('generalSetting')) {
    function generalSetting()
    {
        return \App\Models\GeneralSetting::select('site_currency', 'opening_balance', 'default_image', 'steadfast_cod_charge')->first();
    }
}

// Function to upload image to Cloudinary and return the URL
function cloudUpload($image, $folder, $old = null)
{
    $cloudFolder = rtrim(env('APP_NAME'), '/').'/'.ltrim($folder, '/');

    // Delete old image
    if ($old) {
        $parsedUrl = parse_url($old, PHP_URL_PATH);  // only path portion of the URL

        // Extract parts after "upload"
        $parts = explode('/', $parsedUrl);
        $uploadIndex = array_search('upload', $parts);

        // Skip all transformations and version (like q_auto, f_auto, v1)
        $filtered = [];
        for ($i = $uploadIndex + 1; $i < count($parts); $i++) {
            if (! preg_match('/^(v\d+|q_auto|f_auto)$/', $parts[$i])) {
                $filtered[] = $parts[$i];
            }
        }

        // Remove file extension from last part
        $last = array_pop($filtered);
        $publicId = pathinfo($last, PATHINFO_FILENAME);
        $filtered[] = $publicId;

        $publicIdWithFolder = implode('/', $filtered);

        // Delete from Cloudinary
        cloudinary()->destroy($publicIdWithFolder);
    }

    // Detect extension
    $originalExtension = strtolower(pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION));
    $isSvg = ($originalExtension === 'svg');

    // Upload new image
    $uploadOptions = [
        'folder' => $cloudFolder,
        'resource_type' => 'image',
    ];

    $response = cloudinary()->upload($image->getRealPath(), $uploadOptions);
    $publicId = $response->getPublicId();

    // Generate final URL
    $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

    $finalUrl = $cloudinary->image($publicId.'.'.($isSvg ? 'svg' : 'webp'))
        ->delivery(\Cloudinary\Transformation\Delivery::quality('auto'))
        ->format('auto')
        ->toUrl();

    return $finalUrl;
}

// Function to generate a Cloudinary image URL with transformations
function cloudImage($url, $width = null, $height = null)
{
    if (! $url) {
        return;
    }

    // If both width and height are provided
    if ($width && $height) {
        $transformation = "c_fill,w_{$width},h_{$height}";
    }
    // If only width is provided
    elseif ($width) {
        $transformation = "c_scale,w_{$width}";
    }
    // If only height is provided
    elseif ($height) {
        $transformation = "c_scale,h_{$height}";
    }
    // No size provided, just optimize
    else {
        $transformation = '';
    }

    // Add quality and format auto
    $transformation .= ($transformation ? ',' : '').'q_auto,f_auto';

    return preg_replace(
        '#/upload/#',
        "/upload/{$transformation}/",
        $url
    );
}

if (! function_exists('get_public_id_from_url')) {
    /**
     * Extract the public ID from a Cloudinary URL.
     *
     * @param  string  $url
     * @return string|null
     */
    function get_public_id_from_url($url)
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

            $pathAfterUpload = substr($path, $uploadPos + strlen('/upload/'));
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

            return pathinfo($final, PATHINFO_DIRNAME).'/'.pathinfo($final, PATHINFO_FILENAME);
        } catch (\Exception $e) {
            \Log::error('Failed to parse Cloudinary URL: '.$e->getMessage());

            return;
        }
    }
}

if (! function_exists('generateUniqueSerial')) {
    /**
     * Generate a unique serial in the format PREFIXddmmyysssNNN
     * e.g., R240624001, INV240624002 (where 24=day, 06=month, 24=year, 01=counter)
     */
    function generateUniqueSerial(string $table, string $column, $prefix = 'R'): string
    {
        $now = now();
        $date = $now->format('d');   // day
        $month = $now->format('m');  // month
        $year = $now->format('y');   // year (last two digits)

        // Find the latest serial for today WITH this prefix (if you wish, adapt for prefix filter)
        $latestSerial = DB::table($table)
            ->whereDate('created_at', $now->toDateString())
            ->where($column, 'like', "{$prefix}{$date}{$month}{$year}%")
            ->orderByDesc('id')
            ->value($column);

        // Extract last 3 digits for increment, fallback to 001
        if ($latestSerial && preg_match('/(\d{3})$/', $latestSerial, $matches)) {
            $lastNumber = (int)$matches[1];
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        // Serial: PREFIX + dd + mm + yy + 3-digit counter
        $serial = "{$prefix}{$date}{$month}{$year}{$newNumber}";

        return $serial;
    }
}    

function customerDue($customerID)
{
    return Transaction::where('customer_id', $customerID)
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->where('transaction_type', 'sales_on_credit')
                    ->where('credit', CommonConstant::CREDIT);
            })->orWhere(function ($q) {
                $q->where('transaction_type', 'payment_received')
                    ->where('transaction_tag', '!=', 'payment_received_1')
                    ->where('debit', CommonConstant::DEBIT);
            });
        })
        ->selectRaw('SUM(CASE 
            WHEN transaction_type = "sales_on_credit" THEN amount 
            WHEN transaction_type = "payment_received" THEN -amount
            ELSE 0 END) as due_amount')
        ->value('due_amount') ?? 0;
}

function currency_format($amount, $currency = null)
{
    if ($currency == null) {
        $currency = generalSetting()->site_currency;
    }

    return $currency.' '.number_format($amount, 2);
}
