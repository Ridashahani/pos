<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\CustomerController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\HelpController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\PosController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\PurchaseController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\StockController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\VariationController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;


Route::get('/', function () {
    return redirect()->route('dashboard');
});
// DEFAULT DASHBOARD & PROFILE
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/in', [StockController::class, 'in'])->name('in');
        Route::get('/in/details/{product}', [StockController::class, 'inDetails'])->name('in.details');
        Route::get('/out', [StockController::class, 'out'])->name('out');
        Route::get('/transfer', [StockController::class, 'transfer'])->name('transfer');
        Route::get('/transfer/create', [StockController::class, 'createTransfer'])->name('transfer.create');
        Route::post('/transfer', [StockController::class, 'storeTransfer'])->name('transfer.store');
        Route::get('/transfer/{transfer}/edit', [StockController::class, 'editTransfer'])->name('transfer.edit');
        Route::put('/transfer/{transfer}', [StockController::class, 'updateTransfer'])->name('transfer.update');
        Route::delete('/transfer/{transfer}', [StockController::class, 'destroyTransfer'])->name('transfer.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::get('/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ====== USERS ======
Route::middleware(['permission:access.users'])->group(function () {
    Route::resource('/users', UserController::class)->except(['show']);
});

// ====== CUSTOMERS ======
Route::middleware(['permission:access.customers'])->group(function () {
    Route::resource('/customers', CustomerController::class);
});

// ====== SUPPLIERS ======
Route::middleware(['permission:access.suppliers'])->group(function () {
    Route::resource('/suppliers', SupplierController::class);
});

// ====== PRODUCTS ======
Route::middleware(['permission:access.products'])->group(function () {
    Route::resource('/variations', VariationController::class)->except(['show']);
    Route::get('/products/import', [ProductController::class, 'importView'])->name('products.importView');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.importStore');
    Route::get('/products/export', [ProductController::class, 'exportData'])->name('products.exportData');
    Route::resource('/products', ProductController::class);
});

// ====== CATEGORY PRODUCTS ======
Route::middleware(['permission:access.categories'])->group(function () {
    Route::resource('/categories', CategoryController::class);
});

// ====== POS ======
Route::middleware(['permission:access.pos'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/add', [PosController::class, 'addCart'])->name('pos.addCart');
    Route::post('/pos/update/{rowId}', [PosController::class, 'updateCart'])->name('pos.updateCart');
    Route::post('/pos/discount/{rowId}', [PosController::class, 'updateDiscount'])->name('pos.updateDiscount');
    Route::get('/pos/delete/{rowId}', [PosController::class, 'deleteCart'])->name('pos.deleteCart');
    Route::post('/pos/customer', [PosController::class, 'storeCustomer'])->name('pos.storeCustomer');
    Route::get('/pos/customers-ajax', [PosController::class, 'searchCustomers'])->name('pos.customers.search');

    Route::post('/pos/invoice/print', [PosController::class, 'printInvoice'])->name('pos.printInvoice');

    // Create Order
    Route::post('/pos/order', [OrderController::class, 'storeOrder'])->name('pos.storeOrder');
});

// ====== ORDERS ======
Route::middleware(['permission:access.sales'])->group(function () {
    Route::get('/orders/pending', [OrderController::class, 'pendingOrders'])->name('order.pendingOrders');
    Route::get('/orders/complete', [OrderController::class, 'completeOrders'])->name('order.completeOrders');
    Route::get('/orders/details/{order_id}', [OrderController::class, 'orderDetails'])->name('order.orderDetails');
    Route::put('/orders/update/status', [OrderController::class, 'updateStatus'])->name('order.updateStatus');
    Route::get('/orders/invoice/download/{order_id}', [OrderController::class, 'invoiceDownload'])->name('order.invoiceDownload');
    Route::get('/orders/receipt/print/{order_id}', [OrderController::class, 'printReceipt'])->name('order.printReceipt');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::get('/purchases/returns', [PurchaseController::class, 'returns'])->name('purchases.returns');
    Route::get('/purchases/{purchaseNo}/return', [PurchaseController::class, 'returnCreate'])->name('purchases.return.create');

    // Pending Due
    Route::get('/pending/due', [OrderController::class, 'pendingDue'])->name('order.pendingDue');
    Route::get('/order/due/{id}', [OrderController::class, 'orderDueAjax'])->name('order.orderDueAjax');
    Route::post('/update/due', [OrderController::class, 'updateDue'])->name('order.updateDue');

    // Stock Management

});
// Branches
Route::resource('branches', BranchController::class);
// Expense
Route::resource('expenses', ExpenseController::class);
// Payments
Route::middleware(['permission:access.payments'])->group(function () {
    Route::resource('payments', PaymentController::class);
});

// ====== HELP ======
Route::middleware('auth')->group(function () {
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});

// ====== ROLE CONTROLLER ======
Route::middleware(['permission:access.roles'])->group(function () {
    // Permissions
    Route::get('/permission', [RoleController::class, 'permissionIndex'])->name('permission.index');
    Route::get('/permission/create', [RoleController::class, 'permissionCreate'])->name('permission.create');
    Route::post('/permission', [RoleController::class, 'permissionStore'])->name('permission.store');
    Route::get('/permission/edit/{id}', [RoleController::class, 'permissionEdit'])->name('permission.edit');
    Route::put('/permission/{id}', [RoleController::class, 'permissionUpdate'])->name('permission.update');
    Route::delete('/permission/{id}', [RoleController::class, 'permissionDestroy'])->name('permission.destroy');

    // Roles
    Route::get('/role', [RoleController::class, 'roleIndex'])->name('role.index');
    Route::get('/role/create', [RoleController::class, 'roleCreate'])->name('role.create');
    Route::post('/role', [RoleController::class, 'roleStore'])->name('role.store');
    Route::get('/role/edit/{id}', [RoleController::class, 'roleEdit'])->name('role.edit');
    Route::put('/role/{id}', [RoleController::class, 'roleUpdate'])->name('role.update');
    Route::delete('/role/{id}', [RoleController::class, 'roleDestroy'])->name('role.destroy');

    // Role Permissions
    Route::get('/role/permission', [RoleController::class, 'rolePermissionIndex'])->name('rolePermission.index');
    Route::get('/role/permission/create', [RoleController::class, 'rolePermissionCreate'])->name('rolePermission.create');
    Route::post('/role/permission', [RoleController::class, 'rolePermissionStore'])->name('rolePermission.store');
    Route::get('/role/permission/{id}', [RoleController::class, 'rolePermissionEdit'])->name('rolePermission.edit');
    Route::put('/role/permission/{id}', [RoleController::class, 'rolePermissionUpdate'])->name('rolePermission.update');
    Route::delete('/role/permission/{id}', [RoleController::class, 'rolePermissionDestroy'])->name('rolePermission.destroy');
});

require __DIR__ . '/auth.php';
