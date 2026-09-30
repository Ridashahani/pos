<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no')->unique();

            $table->foreignId('branch_id')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('user_id')->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->dateTime('sale_date')->index();

            $table->unsignedInteger('total_products')->default(0);

            $table->decimal('sub_total', 15, 2)->default(0);

            $table->decimal('discount', 15, 2)->default(0);

            $table->decimal('vat', 15, 2)->default(0);

            $table->decimal('total', 15, 2)->default(0);

            $table->decimal('pay_amount', 15, 2)->default(0);

            $table->decimal('due_amount', 15, 2)->default(0);

            $table->string('payment_type')->nullable();

            $table->string('sale_status')->default('pending')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('orders');
    }
};
