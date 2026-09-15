<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index', ['purchases' => $this->purchaseRecords()]);
    }

    public function create()
    {
        return view('purchases.create');
    }

    public function returns()
    {
        return view('purchases.returns', ['purchases' => $this->purchaseRecords()]);
    }

    public function returnCreate(string $purchaseNo)
    {
        $purchase = collect($this->purchaseRecords())->firstWhere('number', $purchaseNo);

        abort_if(!$purchase, 404);

        return view('purchases.return-create', ['purchase' => $purchase]);
    }

    private function purchaseRecords(): array
    {
        return [
            ['number' => 'PUR-1001', 'supplier' => 'Metro Wholesale', 'date' => '2026-09-12', 'items' => 86, 'total' => 'PKR 4,280.00', 'payment' => 'Bank Transfer', 'status' => 'Paid', 'reason' => 'Damaged charger boxes received'],
            ['number' => 'PUR-1002', 'supplier' => 'Fresh Foods Ltd.', 'date' => '2026-09-10', 'items' => 124, 'total' => 'PKR 7,650.00', 'payment' => 'Cash', 'status' => 'Paid', 'reason' => 'Wrong mobile covers delivered'],
            ['number' => 'PUR-1003', 'supplier' => 'City Distributors', 'date' => '2026-09-08', 'items' => 72, 'total' => 'PKR 5,500.00', 'payment' => 'Credit', 'status' => 'Pending', 'reason' => 'Screen protectors did not match order'],
            ['number' => 'PUR-1004', 'supplier' => 'Global Supplies', 'date' => '2026-09-05', 'items' => 55, 'total' => 'PKR 3,920.00', 'payment' => 'Bank Transfer', 'status' => 'Paid', 'reason' => 'Power banks failed quality check'],
            ['number' => 'PUR-1005', 'supplier' => 'Metro Wholesale', 'date' => '2026-09-02', 'items' => 98, 'total' => 'PKR 3,500.00', 'payment' => 'Credit', 'status' => 'Pending', 'reason' => 'Quantity was more than ordered'],
        ];
    }
}
