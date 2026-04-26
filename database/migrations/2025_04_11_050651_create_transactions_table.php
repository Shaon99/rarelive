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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreignId('from_account_id')->nullable()->constrained('payment_methods')->onDelete('cascade');
            $table->foreignId('to_account_id')->nullable()->constrained('payment_methods')->onDelete('cascade');
            $table->string('steadfast_account')->nullable();
            $table->string('transaction_type')->nullable();
            $table->double('account_balance_was', 15, 2)->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->string('note')->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('debit')->nullable();
            $table->string('credit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
