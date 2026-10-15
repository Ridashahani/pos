<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the hot POS queries:
 *  - product search:  WHERE deleted_at IS NULL AND stock > 0 AND (expire_date ...) ORDER BY name
 *  - customer search: ORDER BY created_at DESC LIMIT 20
 *  - branch check:    user_branch lookup on every request for non-admin users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! $this->hasIndex('products', 'products_name_index')) {
                $table->index('name', 'products_name_index');
            }
            if (! $this->hasIndex('products', 'products_pos_sellable_index')) {
                $table->index(['deleted_at', 'stock', 'expire_date'], 'products_pos_sellable_index');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (! $this->hasIndex('customers', 'customers_created_at_index')) {
                $table->index('created_at', 'customers_created_at_index');
            }
        });

        Schema::table('user_branch', function (Blueprint $table) {
            if (! $this->hasIndex('user_branch', 'user_branch_user_id_branch_id_index')) {
                $table->index(['user_id', 'branch_id'], 'user_branch_user_id_branch_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if ($this->hasIndex('products', 'products_name_index')) {
                $table->dropIndex('products_name_index');
            }
            if ($this->hasIndex('products', 'products_pos_sellable_index')) {
                $table->dropIndex('products_pos_sellable_index');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if ($this->hasIndex('customers', 'customers_created_at_index')) {
                $table->dropIndex('customers_created_at_index');
            }
        });

        Schema::table('user_branch', function (Blueprint $table) {
            if ($this->hasIndex('user_branch', 'user_branch_user_id_branch_id_index')) {
                $table->dropIndex('user_branch_user_id_branch_id_index');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return ! empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]));
    }
};