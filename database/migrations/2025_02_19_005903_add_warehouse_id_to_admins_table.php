<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->after('id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
