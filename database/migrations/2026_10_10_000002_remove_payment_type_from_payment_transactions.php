<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payment_transactions', 'payment_type')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropColumn('payment_type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('payment_transactions', 'payment_type')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->string('payment_type')->nullable()->after('type');
            });
        }
    }
};
