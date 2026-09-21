<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payment_transactions', 'notes')
            && ! Schema::hasColumn('payment_transactions', 'description')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->renameColumn('notes', 'description');
            });
        }

        if (Schema::hasColumn('payment_transactions', 'reference')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropColumn('reference');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_transactions', 'description')
            && ! Schema::hasColumn('payment_transactions', 'notes')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->renameColumn('description', 'notes');
            });
        }

        if (! Schema::hasColumn('payment_transactions', 'reference')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->string('reference', 100)->nullable();
            });
        }
    }
};