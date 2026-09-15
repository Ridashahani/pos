<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;

class StockController extends Controller
{
    private array $stockIn = [
        ['date' => '15 Sep 2026', 'reference' => 'SIN-1001', 'product' => 'Wireless Barcode Scanner', 'quantity' => 25, 'unit_cost' => 65.00, 'supplier' => 'Tech Supplies'],
        ['date' => '13 Sep 2026', 'reference' => 'SIN-1000', 'product' => 'Thermal Receipt Paper', 'quantity' => 100, 'unit_cost' => 3.50, 'supplier' => 'Office Mart'],
        ['date' => '10 Sep 2026', 'reference' => 'SIN-0999', 'product' => 'Cash Drawer', 'quantity' => 8, 'unit_cost' => 85.00, 'supplier' => 'Retail Equip'],
        ['date' => '08 Sep 2026', 'reference' => 'SIN-0998', 'product' => 'USB-C Charging Cable', 'quantity' => 50, 'unit_cost' => 7.25, 'supplier' => 'Tech Supplies'],
    ];

    private array $stockOut = [
        ['date' => '15 Sep 2026', 'reference' => 'SOUT-2041', 'product' => 'Thermal Receipt Paper', 'quantity' => 12, 'unit_price' => 5.00, 'destination' => 'Main Store'],
        ['date' => '14 Sep 2026', 'reference' => 'SOUT-2040', 'product' => 'USB-C Charging Cable', 'quantity' => 7, 'unit_price' => 12.50, 'destination' => 'POS Counter'],
        ['date' => '12 Sep 2026', 'reference' => 'SOUT-2039', 'product' => 'Wireless Barcode Scanner', 'quantity' => 3, 'unit_price' => 95.00, 'destination' => 'Main Store'],
        ['date' => '09 Sep 2026', 'reference' => 'SOUT-2038', 'product' => 'Cash Drawer', 'quantity' => 1, 'unit_price' => 125.00, 'destination' => 'POS Counter'],
    ];

    private array $stockTransfers = [
        ['date' => '15 Sep 2026', 'reference' => 'TRF-3012', 'product' => 'Wireless Barcode Scanner', 'quantity' => 4, 'from' => 'Main Store', 'to' => 'Branch A', 'status' => 'In Transit'],
        ['date' => '13 Sep 2026', 'reference' => 'TRF-3011', 'product' => 'Thermal Receipt Paper', 'quantity' => 20, 'from' => 'Warehouse', 'to' => 'Main Store', 'status' => 'Completed'],
        ['date' => '11 Sep 2026', 'reference' => 'TRF-3010', 'product' => 'USB-C Charging Cable', 'quantity' => 10, 'from' => 'Main Store', 'to' => 'Branch B', 'status' => 'Completed'],
        ['date' => '07 Sep 2026', 'reference' => 'TRF-3009', 'product' => 'Cash Drawer', 'quantity' => 2, 'from' => 'Warehouse', 'to' => 'Main Store', 'status' => 'Pending'],
    ];

    public function in()
    {
        $products = Product::with('category')
            ->orderBy('id')
            ->limit(25)
            ->get();

        $rows = $products->map(function (Product $product): array {
            return [
                'product_id' => $product->id,
                'date' => $product->buying_date ?: $product->created_at->format('d M Y'),
                'reference' => $product->code,
                'product' => $product->name,
                'brand' => $product->brand,
                'model' => $product->model,
                'imei' => $product->imei,
                'quantity' => $product->stock,
                'unit_cost' => $product->buying_price,
                'category' => $product->category->name,
            ];
        })->all();

        return view('stock.index', $this->pageData('Stock-In', 'stock-in', $rows));
    }

    public function inDetails()
    {
        return view('stock.details', [
            'products' => Product::with('category')
                ->orderBy('id')
                ->limit(25)
                ->get(),
        ]);
    }

    public function out()
    {
        return view('stock.index', $this->pageData('Stock-out', 'stock-out', $this->stockOut));
    }

    public function transfer()
    {
        return view('stock.index', $this->pageData('Stock-Transfer', 'stock-transfer', $this->stockTransfers));
    }

    private function pageData(string $title, string $type, array $rows): array
    {
        return [
            'title' => $title,
            'type' => $type,
            'rows' => $rows,
            'total_products' => count($rows),
            'total_units' => array_sum(array_column($rows, 'quantity')),
            'this_month' => count($rows),
            'pending' => $type === 'stock-transfer' ? 2 : 0,
        ];
    }
}
