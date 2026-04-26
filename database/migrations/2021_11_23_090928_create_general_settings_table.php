<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sitename')->nullable();
            $table->string('site_currency', 10)->nullable();
            $table->string('site_email')->nullable();
            $table->string('email_method')->default('php')->nullable();
            $table->text('email_config')->nullable();
            $table->text('default_image')->nullable();
            $table->string('favicon')->nullable();
            $table->string('logo')->nullable();
            $table->string('invoice_logo')->nullable();
            $table->string('site_phone')->nullable();
            $table->string('site_address')->nullable();
            $table->string('steadfast_api_key')->nullable();
            $table->string('steadfast_api_secret')->nullable();
            $table->double('steadfast_cod_charge', 28, 2)->nullable()->default(1);
            $table->tinyInteger('enable_online_deliver')->nullable()->default(0);
            $table->tinyInteger('cloudinary_enable')->nullable()->default(0);
            $table->tinyInteger('pos_platform_on_off')->nullable()->default(0);
            $table->tinyInteger('pos_lead_on_off')->nullable()->default(0);
            $table->text('invoice_greeting')->nullable();
            $table->double('opening_balance', 28, 2)->default(0);
            $table->text('invoice_header_note')->nullable();
            $table->tinyInteger('pos_invoice_on_off')->default(0);
            $table->tinyInteger('fraud_check_on_off')->default(0);
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_settings');
    }
}
