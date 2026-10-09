<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in', function (Blueprint $table): void {
            if (! Schema::hasColumn('stock_in', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('stock_in', 'variation_id')) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('branch_id');
            }
            if (! Schema::hasColumn('stock_in', 'batch_no')) {
                $table->string('batch_no')->nullable()->after('purchase_id');
            }
            if (! Schema::hasColumn('stock_in', 'remaining_quantity')) {
                $table->unsignedInteger('remaining_quantity')->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('stock_in', 'purchase_id_replacement')) {
                $table->unsignedBigInteger('purchase_id_replacement')->nullable();
            }
        });

        DB::table('stock_in')->orderBy('id')->chunkById(500, function ($stockIns): void {
            foreach ($stockIns as $stockIn) {
                $stockInData = (array) $stockIn;
                $purchaseId = $stockInData['purchase_id_replacement'] ?? $stockInData['purchase_id'] ?? null;
                $purchase = DB::table('purchases')->where('id', $purchaseId)->first();
                $product = DB::table('products')->where('id', $stockIn->product_id)->first();

                if (! $purchase || ! $product) {
                    throw new RuntimeException("Cannot migrate stock-in row {$stockIn->id}: its purchase or product is missing.");
                }

                DB::table('stock_in')->where('id', $stockIn->id)->update([
                    'branch_id' => $purchase->branch_id,
                    'variation_id' => $product->variation_id,
                    'batch_no' => $purchase->purchase_no ?: 'STK-' . $stockIn->id,
                    'remaining_quantity' => $stockInData['remaining_quantity'] ?? $stockInData['remaining_qty'],
                    'purchase_id_replacement' => $purchaseId,
                ]);
            }
        });

        if ($this->hasForeignKey('purchase_id')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->dropForeign(['purchase_id']);
            });
        }

        if (! $this->hasIndex('stock_in_product_id_remaining_quantity_index')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->index(['product_id', 'remaining_quantity']);
            });
        }

        if ($this->hasIndex('stock_in_product_id_remaining_qty_index')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->dropIndex(['product_id', 'remaining_qty']);
            });
        }

        $legacyColumns = array_values(array_filter(
            ['purchase_id', 'imei', 'condition', 'remaining_qty', 'sale_price'],
            fn (string $column): bool => Schema::hasColumn('stock_in', $column)
        ));

        if ($legacyColumns !== []) {
            Schema::table('stock_in', function (Blueprint $table) use ($legacyColumns): void {
                $table->dropColumn($legacyColumns);
            });
        }

        if (Schema::hasColumn('stock_in', 'purchase_id_replacement') && ! Schema::hasColumn('stock_in', 'purchase_id')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->renameColumn('purchase_id_replacement', 'purchase_id');
            });
        } elseif (Schema::hasColumn('stock_in', 'purchase_id_replacement')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->dropColumn('purchase_id_replacement');
            });
        }

        $this->setRequiredColumnsNotNull('branch_id', 'batch_no', 'remaining_quantity');

        if (! $this->hasForeignKey('branch_id')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            });
        }
        if (! $this->hasForeignKey('variation_id')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->foreign('variation_id')->references('id')->on('variations')->nullOnDelete();
            });
        }
        if (! $this->hasForeignKey('purchase_id')) {
            Schema::table('stock_in', function (Blueprint $table): void {
                $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::table('stock_in')->whereNull('purchase_id')->exists()) {
            throw new RuntimeException('Cannot restore the previous stock-in schema while rows have no purchase.');
        }

        Schema::table('stock_in', function (Blueprint $table): void {
            $table->string('imei')->nullable();
            $table->string('condition')->nullable();
            $table->unsignedInteger('remaining_qty')->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
        });

        DB::table('stock_in')->orderBy('id')->chunkById(500, function ($stockIns): void {
            foreach ($stockIns as $stockIn) {
                $product = DB::table('products')->where('id', $stockIn->product_id)->first();

                DB::table('stock_in')->where('id', $stockIn->id)->update([
                    'imei' => $product?->imei,
                    'condition' => 'new',
                    'remaining_qty' => $stockIn->remaining_quantity,
                    'sale_price' => $product?->selling_price ?? 0,
                ]);
            }
        });

        Schema::table('stock_in', function (Blueprint $table): void {
            $table->dropForeign(['purchase_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['variation_id']);
            $table->dropIndex(['product_id', 'remaining_quantity']);
            $table->dropColumn(['branch_id', 'variation_id', 'batch_no', 'remaining_quantity']);
        });

        $this->setRequiredColumnsNotNull('remaining_qty', 'purchase_id');

        Schema::table('stock_in', function (Blueprint $table): void {
            $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
            $table->index(['product_id', 'remaining_qty']);
        });
    }

    private function setRequiredColumnsNotNull(string ...$columns): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $definitions = [
                'branch_id' => '`branch_id` BIGINT UNSIGNED NOT NULL',
                'batch_no' => '`batch_no` VARCHAR(255) NOT NULL',
                'remaining_quantity' => '`remaining_quantity` INT UNSIGNED NOT NULL',
                'remaining_qty' => '`remaining_qty` INT UNSIGNED NOT NULL',
                'purchase_id' => '`purchase_id` BIGINT UNSIGNED NOT NULL',
            ];

            $clauses = array_map(fn (string $column): string => 'MODIFY ' . $definitions[$column], $columns);
            DB::statement('ALTER TABLE `stock_in` ' . implode(', ', $clauses));

            return;
        }

        if ($driver === 'pgsql') {
            foreach ($columns as $column) {
                DB::statement('ALTER TABLE stock_in ALTER COLUMN ' . $column . ' SET NOT NULL');
            }

            return;
        }

        throw new RuntimeException('The stock-in schema migration supports MySQL and PostgreSQL databases.');
    }

    private function hasForeignKey(string $column): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'stock_in')
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }

    private function hasIndex(string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'stock_in')
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};