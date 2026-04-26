<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('admins')->onDelete('cascade')->onUpdate('cascade');

            // Add warehouse_id foreign key
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')
                ->onDelete('cascade')->onUpdate('cascade');

            // Add customer_id foreign key
            $table->bigInteger('customer_id')->unsigned()->nullable()->after('warehouse_id');
            $table->foreign('customer_id')->references('id')->on('customers')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->bigInteger('lead_id')->unsigned()->nullable()->after('id');
            $table->foreign('lead_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['customer_id']);

            $table->dropForeign(['lead_id']);
            // Then drop columns
            $table->dropColumn(['warehouse_id', 'customer_id']);
        });
    }
};
