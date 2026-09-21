<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_account_id')->constrained('payment_accounts')->cascadeOnDelete();
            $table->string('type');
            $table->string('customer_name');
            $table->string('customer_phone', 30)->nullable();
            $table->string('recipient_name')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('commission', 15, 2)->default(0);
            $table->dateTime('transaction_date')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
