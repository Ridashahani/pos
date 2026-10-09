<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('sale_details') ? 'sale_details' : 'order_details';

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('currency', 3)->default('PKR')->after('unit_price');
        });
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('sale_details') ? 'sale_details' : 'order_details';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
