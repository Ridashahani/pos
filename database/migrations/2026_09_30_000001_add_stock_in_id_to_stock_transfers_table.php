<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table): void {
            $table->foreignId('stock_in_id')
                ->nullable()
                ->after('product_id')
                ->constrained('stock_in')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('stock_in_id');
        });
    }
};