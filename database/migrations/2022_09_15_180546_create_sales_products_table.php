<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sales_id')->unsigned()->nullable();
            $table->foreign('sales_id')->references('id')->on('sales')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('combo_id')->unsigned()->nullable();
            $table->foreign('combo_id')->references('id')->on('products')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->double('quantity', 28, 2)->nullable();
            $table->double('unit_price', 28, 2)->nullable();
            $table->double('total', 28, 2)->nullable();
            $table->double('profit', 28, 2)->nullable();
            $table->double('discount', 28, 2)->nullable();
            $table->string('discount_type', 20)->nullable();
            $table->integer('returned_quantity')->nullable();
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
        Schema::dropIfExists('sales_products');
    }
}
