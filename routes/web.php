<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\Dashboard\BrandController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\HelpController;
use App\Http\Controllers\Dashboard\PosController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\PurchaseController;
use App\Http\Controllers\Dashboard\PurchaseReturnController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\SaleController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\StockController;
use App\Http\Controllers\Dashboard\SubcategoryController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\VariationController;
use App\Http\Controllers\Dashboard\UnitController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\BranchSelectionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserBranchController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('dashboard');
});
// DEFAULT DASHBOARD & PROFILE
Route::middleware('auth')->group(function () {
    // Active Branch Selection Routes
    Route::get('/select-branch', [BranchSelectionController::class, 'select'])->name('branch.select');
    Route::post('/select-branch', [BranchSelectionController::class, 'setActive'])->name('branch.set-active');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

    Route::middleware('permission:access.stocks')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/in', [StockController::class, 'in'])->name('in');
        Route::get('/in/details/{stockIn}', [StockController::class, 'inDetails'])->name('in.details');
        Route::get('/out', [StockController::class, 'out'])->name('out');
        Route::get('/sold-items', [StockController::class, 'soldItems'])->name('sold-items');
        Route::get('/out-of-stock', [StockController::class, 'outOfStock'])->name('out-of-stock');
        Route::get('/transfer', [StockController::class, 'transfer'])->name('transfer');
    });
    Route::middleware('permission:create.stocks')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/transfer/create', [StockController::class, 'createTransfer'])->name('transfer.create');
        Route::post('/transfer', [StockController::class, 'storeTransfer'])->name('transfer.store');
    });
    Route::middleware('permission:edit.stocks')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/transfer/{transfer}/edit', [StockController::class, 'editTransfer'])->name('transfer.edit');
        Route::put('/transfer/{transfer}', [StockController::class, 'updateTransfer'])->name('transfer.update');
    });
    Route::middleware('permission:delete.stocks')->prefix('stock')->name('stock.')->group(function () {
        Route::delete('/sold-items', [StockController::class, 'clearSoldItems'])->name('sold-items.clear');
        Route::delete('/sold-items/{soldItem}', [StockController::class, 'destroySoldItem'])->name('sold-items.destroy');
        Route::delete('/out-of-stock', [StockController::class, 'clearOutOfStock'])->name('out-of-stock.clear');
        Route::delete('/out-of-stock/{stockIn}', [StockController::class, 'destroyOutOfStock'])->name('out-of-stock.destroy');
        Route::delete('/transfer/{transfer}', [StockController::class, 'destroyTransfer'])->name('transfer.destroy');
        Route::delete('/transfer', [StockController::class, 'clearTransfers'])->name('transfer.clear');
    });

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::get('/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Register each resource action under its own permission.
$registerCrudRoutes = function (string $uri, string $controller, string $permission, array $only = []) {
    $actions = [
        'create' => ['create', 'store'],
        'access' => ['index', 'show'],
        'edit' => ['edit', 'update'],
        'delete' => ['destroy'],
    ];

    foreach ($actions as $action => $methods) {
        $methods = array_values(array_intersect($methods, $only ?: array_merge(...array_values($actions))));
        if ($methods) {
            Route::middleware("permission:{$action}.{$permission}")
                ->group(fn () => Route::resource($uri, $controller)->only($methods));
        }
    }
};

// ====== USERS ======
$registerCrudRoutes('/users', UserController::class, 'users', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::middleware('permission:access.users')->get('/user-branch', [UserBranchController::class, 'index'])->name('user-branch.index');
Route::middleware('permission:edit.users')->get('/user-branch/{user}/edit', [UserBranchController::class, 'edit'])->name('user-branch.edit');
Route::middleware('permission:edit.users')->put('/user-branch/{user}', [UserBranchController::class, 'update'])->name('user-branch.update');
Route::middleware('permission:delete.users')->delete('/user-branch/{user}/{branch}', [UserBranchController::class, 'destroy'])->name('user-branch.destroy');

// ====== CUSTOMERS ======
$registerCrudRoutes('/customers', CustomerController::class, 'customers');

// ====== SUPPLIERS ======
$registerCrudRoutes('/suppliers', SupplierController::class, 'suppliers');

// ====== PRODUCTS ======
$registerCrudRoutes('/variations', VariationController::class, 'products', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
$registerCrudRoutes('/units', UnitController::class, 'products', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
$registerCrudRoutes('/products', ProductController::class, 'products');
Route::middleware('permission:access.products')->get('/products/export', [ProductController::class, 'exportData'])->name('products.exportData');
Route::middleware('permission:create.products')->get('/products/import', [ProductController::class, 'importView'])->name('products.importView');
Route::middleware('permission:create.products')->post('/products/import', [ProductController::class, 'importStore'])->name('products.importStore');

// ====== CATEGORY PRODUCTS ======
$registerCrudRoutes('/categories', CategoryController::class, 'categories');
$registerCrudRoutes('/subcategories', SubcategoryController::class, 'categories', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
$registerCrudRoutes('/brands', BrandController::class, 'categories', ['index', 'create', 'store', 'edit', 'update', 'destroy']);

// ====== POS ======
Route::middleware(['permission:access.pos'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/add', [PosController::class, 'addCart'])->name('pos.addCart');
    Route::post('/pos/update/{rowId}', [PosController::class, 'updateCart'])->name('pos.updateCart');
    Route::post('/pos/discount/{rowId}', [PosController::class, 'updateDiscount'])->name('pos.updateDiscount');
    Route::post('/pos/tax-rate/{rowId}', [PosController::class, 'updateTaxRate'])->name('pos.updateTaxRate');
    Route::get('/pos/delete/{rowId}', [PosController::class, 'deleteCart'])->name('pos.deleteCart');
    Route::post('/pos/customer', [PosController::class, 'storeCustomer'])->name('pos.storeCustomer');
    Route::get('/pos/customers-ajax', [PosController::class, 'searchCustomers'])->name('pos.customers.search');

    Route::post('/pos/invoice/print', [PosController::class, 'printInvoice'])->name('pos.printInvoice');
    // Create Sale
    Route::post('/pos/sale', [SaleController::class, 'storeSale'])->name('pos.storeSale');
});

// ====== SALES ======
Route::middleware('permission:access.sales')->group(function () {
    Route::get('/sales/pending', [SaleController::class, 'pendingSales'])->name('sale.pendingSales');
    Route::get('/sales/complete', [SaleController::class, 'completeSales'])->name('sale.completeSales');
    Route::get('/sales/details/{sale_id}', [SaleController::class, 'saleDetails'])->name('sale.saleDetails');
    Route::get('/sales/invoice/download/{sale_id}', [SaleController::class, 'invoiceDownload'])->name('sale.invoiceDownload');
    Route::get('/sales/receipt/print/{sale_id}', [SaleController::class, 'printReceipt'])->name('sale.printReceipt');
    Route::get('/sales/pending-due', [SaleController::class, 'pendingDue'])->name('sale.pendingDue');
    Route::get('/sale/due/{id}', [SaleController::class, 'saleDueAjax'])->name('sale.saleDueAjax');
    Route::get('/purchases/returns', [PurchaseReturnController::class, 'index'])->name('purchases.returns');
    Route::get('/purchases/returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchases.returns.show');
});
Route::middleware('permission:edit.sales')->group(function () {
    Route::put('/sales/update/status', [SaleController::class, 'updateStatus'])->name('sale.updateStatus');
    Route::post('/sales/{sale}/return', [SaleController::class, 'returnSale'])->name('sale.return');
    Route::post('/sales/update/due', [SaleController::class, 'updateDue'])->name('sale.updateDue');
});
Route::middleware('permission:create.sales')->get('/purchases/{purchase}/return', [PurchaseReturnController::class, 'create'])->name('purchases.return.create');
Route::middleware('permission:create.sales')->post('/purchases/{purchase}/return', [PurchaseReturnController::class, 'store'])->name('purchases.return.store');
$registerCrudRoutes('purchases', PurchaseController::class, 'sales');
$registerCrudRoutes('branches', BranchController::class, 'stocks');
$registerCrudRoutes('expenses', ExpenseController::class, 'stocks');
$registerCrudRoutes('payment-accounts', PaymentAccountController::class, 'payments', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
$registerCrudRoutes('payments', PaymentController::class, 'payments');
// privacy policy and terms of use of service
Route::get('/privacy-policy', function () {
    return view('backend.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('backend.terms-of-service');
})->name('terms-of-service');

// ====== HELP ======
Route::middleware('auth')->group(function () {
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});

// ====== ROLE CONTROLLER ======
$registerCrudRoutes('/settings', SettingController::class, 'roles', ['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::middleware('permission:access.roles')->group(function () {
    Route::get('/permission', [RoleController::class, 'permissionIndex'])->name('permission.index');
    Route::get('/role', [RoleController::class, 'roleIndex'])->name('role.index');
    Route::get('/role/permission', [RoleController::class, 'rolePermissionIndex'])->name('rolePermission.index');
});
Route::middleware('permission:create.roles')->group(function () {
    Route::get('/permission/create', [RoleController::class, 'permissionCreate'])->name('permission.create');
    Route::post('/permission', [RoleController::class, 'permissionStore'])->name('permission.store');
    Route::get('/role/create', [RoleController::class, 'roleCreate'])->name('role.create');
    Route::post('/role', [RoleController::class, 'roleStore'])->name('role.store');
    Route::get('/role/permission/create', [RoleController::class, 'rolePermissionCreate'])->name('rolePermission.create');
    Route::post('/role/permission', [RoleController::class, 'rolePermissionStore'])->name('rolePermission.store');
});
Route::middleware('permission:edit.roles')->group(function () {
    Route::get('/permission/edit/{id}', [RoleController::class, 'permissionEdit'])->name('permission.edit');
    Route::put('/permission/{id}', [RoleController::class, 'permissionUpdate'])->name('permission.update');
    Route::get('/role/edit/{id}', [RoleController::class, 'roleEdit'])->name('role.edit');
    Route::put('/role/{id}', [RoleController::class, 'roleUpdate'])->name('role.update');
    Route::get('/role/permission/{id}', [RoleController::class, 'rolePermissionEdit'])->name('rolePermission.edit');
    Route::put('/role/permission/{id}', [RoleController::class, 'rolePermissionUpdate'])->name('rolePermission.update');
});
Route::middleware('permission:delete.roles')->group(function () {
    Route::delete('/permission/{id}', [RoleController::class, 'permissionDestroy'])->name('permission.destroy');
    Route::delete('/role/{id}', [RoleController::class, 'roleDestroy'])->name('role.destroy');
    Route::delete('/role/permission/{id}', [RoleController::class, 'rolePermissionDestroy'])->name('rolePermission.destroy');
});

require __DIR__ . '/auth.php';
