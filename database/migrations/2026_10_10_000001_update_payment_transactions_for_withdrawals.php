<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('recipient_phone', 30)->nullable()->after('recipient_name');
            $table->string('payment_type')->nullable()->after('type');
            $table->string('customer_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('payment_transactions')
            ->whereNull('customer_name')
            ->update(['customer_name' => 'Cash Withdrawal']);

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['recipient_phone', 'payment_type']);
            $table->string('customer_name')->nullable(false)->change();
        });
    }
};
