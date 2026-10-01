<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('order_details', 'sale_details');

        Schema::table('sale_details', function (Blueprint $table) {
            $table->renameColumn('order_id', 'sale_id');
        });
    }

    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->renameColumn('sale_id', 'order_id');
        });

        Schema::rename('sale_details', 'order_details');
    }
};
