<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('transactionable_id')->nullable()->after('id');
            $table->string('transactionable_type')->nullable()->after('transactionable_id');
            $table->integer('payment_method_id')->nullable()->after('transactionable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transactionable_id', 'transactionable_type']);
        });
    }
};
