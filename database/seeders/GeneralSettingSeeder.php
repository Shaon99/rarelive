<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('general_settings')->insert([
            'id' => 1,
            'sitename' => 'OptimoSell',
            'site_currency' => '৳',
            'site_email' => 'noreply@optimosell.com',
            'email_method' => 'smtp',
            'email_config' => json_encode([
                'smtp_host' => 'sandbox.smtp.mailtrap.io',
                'smtp_username' => '910ef84b02183a',
                'smtp_password' => '8d67c568cf8301',
                'smtp_port' => '2525',
                'smtp_encryption' => 'tls',
            ]),
            'default_image' => '670ae01a126201728765978.png',
            'favicon' => '673ced00070791732046080.png',
            'logo' => '673cecff002f81732046079.png',
            'invoice_logo' => '6745e96086eec1732634976.png',
            'site_phone' => '01303552819',
            'site_address' => 'Dhaka, Bangladesh',
            'invoice_greeting' => 'Thank you for your purchases',
            'website' => 'http://optimosell.com',
            'invoice_header_note' => 'Driven by Trust, Powered by Excellence!',
            'opening_balance' => 0.00,
            'created_at' => '2024-10-12 14:44:19',
            'updated_at' => '2025-02-05 20:44:07',
        ]);
    }
}
