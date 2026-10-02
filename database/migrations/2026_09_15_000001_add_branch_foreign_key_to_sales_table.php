<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'branch_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            });

            return;
        }

        $foreignKey = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND REFERENCED_TABLE_NAME = ?',
            ['sales', 'branch_id', 'branches']
        );

        if ($foreignKey) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $foreignKey = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND REFERENCED_TABLE_NAME = ?',
            ['sales', 'branch_id', 'branches']
        );

        if ($foreignKey) {
            Schema::table('sales', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey->CONSTRAINT_NAME);
            });
        }
    }
};