<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            /* The line `->unsignedBigInteger('created_by'); // references suppliers table` in the migration
            code is defining a column named `created_by` in the `purchase_returns` table. This column is of type
            `unsignedBigInteger`, which typically stores an unsigned integer value. The comment `// references
            suppliers table` indicates that this column is intended to reference the `id` column in the
            `suppliers` table. */
            $table->id();
            $table->string('reference_no')->nullable();
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
            $table->unsignedBigInteger('created_by'); // references suppliers table
            $table->foreign('warehouse_id')->references('id')->on('warehouses')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('supplier_id')->unsigned()->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->string('purchase_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->tinyInteger('order_status')->nullable();
            $table->double('discount')->nullable();
            $table->double('other_cost')->nullable();
            $table->double('total_qty')->nullable();
            $table->double('sub_total')->nullable();
            $table->double('grand_total')->nullable();
            $table->double('paid_amount')->nullable();
            $table->double('due_amount')->nullable();
            $table->tinyInteger('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('admins')->onDelete('cascade');

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
        Schema::dropIfExists('purchases');
    }
}
