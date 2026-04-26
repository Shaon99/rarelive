<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->integer('created_by')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('unit_id')->nullable();
            $table->double('average_unit_price')->nullable();
            $table->double('purchase_price')->nullable();
            $table->double('discount')->nullable();
            $table->double('vat')->nullable();
            $table->double('tax')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_date_range')->nullable();
            $table->double('sale_price')->nullable();
            $table->double('whole_sale_price')->nullable();
            $table->double('average_stock_price')->nullable();
            $table->integer('low_quantity_alert')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('is_active')->comment('1=Yes,0=No')->default(0);
            $table->tinyInteger('has_variation')->default(0);
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
        Schema::dropIfExists('products');
    }
}
