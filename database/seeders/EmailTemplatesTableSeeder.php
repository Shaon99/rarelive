<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailTemplate::insert([
            [
                'id' => 1,
                'name' => 'PASSWORD_RESET',
                'subject' => 'Reset Password',
                'template' => '<p>Dear {username},</p><p><strong>Enter the 6-digit Power Reset Code: {otp}</strong></p><p>Best regards,</p><p><br>{sent_from}</p>',
                'meaning' => '{"username":"Email Receiver Name","otp":"Email Verification Code","sent_from":"Email Sent from"}',
                'created_at' => '2023-03-06 21:46:51',
                'updated_at' => '2024-02-17 20:07:34',
            ],
        ]);
    }
}
