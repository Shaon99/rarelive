<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            // Step 1: Add the column
            $table->unsignedBigInteger('employee_id')->nullable()->after('id');

            // Step 2: Add the foreign key constraint
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            // Drop foreign key first, then column
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
