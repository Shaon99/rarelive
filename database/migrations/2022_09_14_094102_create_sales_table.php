<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('consignment_id')->index()->nullable();
            $table->string('tracking_code')->index()->nullable();
            $table->string('status')->index()->nullable();
            $table->string('return_status')->nullable();
            $table->string('platform')->index()->nullable();
            $table->index(['payment_status', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->double('total_qty')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('system_delivery_charge')->nullable();
            $table->double('cod_charge')->nullable();
            $table->double('sub_total')->nullable();
            $table->double('grand_total')->nullable();
            $table->double('paid_amount')->nullable();
            $table->double('due_amount')->nullable();
            $table->double('discount')->nullable();
            $table->tinyInteger('payment_status')->nullable();
            $table->string('system_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('note')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('courier_name')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('sales');
    }
}
