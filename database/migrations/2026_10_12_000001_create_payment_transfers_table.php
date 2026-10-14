<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_payment_account_id')->constrained('payment_accounts')->cascadeOnDelete();
            $table->foreignId('to_payment_account_id')->constrained('payment_accounts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->dateTime('transfer_date')->index();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transfers');
    }
};
