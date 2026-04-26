<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('from_warehouse_id')->unsigned()->nullable();
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('to_warehouse_id')->unsigned()->nullable();
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('quantity')->nullable();
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
        Schema::dropIfExists('transfers');
    }
}
