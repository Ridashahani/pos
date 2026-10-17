<div class="iq-sidebar sidebar-default ">
    <div class="iq-sidebar-logo d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="pos-brand">
            <img src="{{ asset('assets/images/logo.png') }}" alt="logo" class="pos-brand-img">
            <span class="pos-brand-text">POSDash</span>
        </a>
        <div class="iq-menu-bt-sidebar ml-0">
            <x-heroicon-o-bars-3 class="wrapper-menu w-8 h-8" />
        </div>
    </div>
    <div class="data-scrollbar" data-scroll="1">
        <nav class="iq-sidebar-menu">
            <ul id="iq-sidebar-toggle" class="iq-menu">
                <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="svg-icon">
                        <x-heroicon-o-home class="w-6 h-6" />
                        <span class="ml-4">Dashboards</span>
                    </a>
                </li>
                @can('access.pos')
                    <li class="{{ Request::is('pos*') ? 'active' : '' }}">
                        <a href="{{ route('pos.index') }}" class="svg-icon">
                            <x-heroicon-o-shopping-cart class="w-6 h-6" />
                            <span class="ml-3">POS</span>
                        </a>
                    </li>
                @endcan

                <hr>

                {{-- PRODUCTS --}}
                @canany(['access.products', 'create.products', 'access.categories', 'create.categories'])
                    @php($productsMenuActive = Request::is('products*') || Request::is('variations*') || Request::is('units*') || Request::is('categories*') || Request::is('subcategories*') || Request::is('brands*'))
                    <li class="{{ $productsMenuActive ? 'active' : '' }}">
                        <a href="#products" class="{{ $productsMenuActive ? '' : 'collapsed' }}" data-toggle="collapse"
                            aria-expanded="{{ $productsMenuActive ? 'true' : 'false' }}">
                            <x-heroicon-o-archive-box class="w-6 h-6" />
                            <span class="ml-3">Products</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="products" class="iq-submenu collapse {{ $productsMenuActive ? 'show' : '' }}"
                            data-parent="#iq-sidebar-toggle">

                            @can('access.products')
                                <li
                                    class="{{ Request::is('products') || (Request::is('products/*') && !Request::is('products/create')) ? 'active' : '' }}">
                                    <a href="{{ route('products.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Products</span>
                                    </a>
                                </li>
                            @endcan

                            @can('access.categories')
                                <li class="{{ Request::is('categories*') ? 'active' : '' }}">
                                    <a href="{{ route('categories.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Categories</span>
                                    </a>
                                </li>
                                <li class="{{ Request::is('subcategories*') ? 'active' : '' }}">
                                    <a href="{{ route('subcategories.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Subcategories</span>
                                    </a>
                                </li>
                                <li class="{{ Request::is('brands*') ? 'active' : '' }}">
                                    <a href="{{ route('brands.index') }}">
                                        <x-heroicon-o-tag class="w-4 h-4" /><span>Brands</span>
                                    </a>
                                </li>
                            @endcan

                            @can('access.products')
                                <li class="{{ Request::is('variations*') ? 'active' : '' }}">
                                    <a href="{{ route('variations.index') }}">
                                        <x-heroicon-o-squares-2x2 class="w-4 h-4" /><span>Variations</span>
                                    </a>
                                </li>
                            @endcan

                            @can('access.products')
                                <li class="{{ Request::is('units*') ? 'active' : '' }}">
                                    <a href="{{ route('units.index') }}">
                                        <x-heroicon-o-scale class="w-4 h-4" /><span>Units</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                {{-- SALES + PURCHASES --}}
                @canany(['access.sales', 'create.sales'])
                    <li>
                        <a href="#sales" class="collapsed" data-toggle="collapse"
                            aria-expanded="{{ Request::is('sales*') || Request::is('pending/due*') ? 'true' : 'false' }}">
                            <x-heroicon-o-shopping-bag class="w-6 h-6" />
                            <span class="ml-3">Sales</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="sales"
                            class="iq-submenu collapse {{ Request::is('sales*') || Request::is('pending/due*') ? 'show' : '' }}"
                            data-parent="#iq-sidebar-toggle">
                            @can('access.sales')
                                <li class="{{ Request::is('sales/pending*') ? 'active' : '' }}">
                                    <a href="{{ route('sale.pendingSales') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Pending Sales</span>
                                    </a>
                                </li>
                                <li class="{{ Request::is('sales/complete*') ? 'active' : '' }}">
                                    <a href="{{ route('sale.completeSales') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Complete Sales</span>
                                    </a>
                                </li>
                                <li
                                    class="{{ Request::is('pending/due*') || Request::is('sales/pending-due*') ? 'active' : '' }}">
                                    <a href="{{ route('sale.pendingDue') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Pending Due</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>

                    <li>
                        <a href="#purchases" class="collapsed" data-toggle="collapse"
                            aria-expanded="{{ Request::is('purchases*') ? 'true' : 'false' }}">
                            <x-heroicon-o-archive-box-arrow-down class="w-6 h-6" />
                            <span class="ml-3">Purchases</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="purchases" class="iq-submenu collapse {{ Request::is('purchases*') ? 'show' : '' }}"
                            data-parent="#iq-sidebar-toggle">
                            @can('access.sales')
                                <li class="{{ Request::is('purchases') ? 'active' : '' }}">
                                    <a href="{{ route('purchases.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>All Purchases</span>
                                    </a>
                                </li>
                            @endcan

                            @can('access.sales')
                                <li class="{{ Request::is('purchases/returns') ? 'active' : '' }}">
                                    <a href="{{ route('purchases.returns') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Purchase Returns</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                {{-- STOCK --}}
                @canany(['access.stocks', 'create.stocks'])
                    <li>
                        <a href="#stock" class="collapsed" data-toggle="collapse"
                            aria-expanded="{{ Request::is('stock*') ? 'true' : 'false' }}">
                            <x-heroicon-o-archive-box-arrow-down class="w-6 h-6" />
                            <span class="ml-3">Stock</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="stock" class="iq-submenu collapse {{ Request::is('stock*') ? 'show' : '' }}"
                            data-parent="#iq-sidebar-toggle">
                            @can('access.stocks')
                                <li class="{{ Request::is('stock/in') ? 'active' : '' }}">
                                    <a href="{{ route('stock.in') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Stock In</span>
                                    </a>
                                </li>
                                <li class="{{ Request::is('stock/sold-items') || Request::is('stock/out') ? 'active' : '' }}">
                                    <a href="{{ route('stock.sold-items') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Sold Items</span>
                                    </a>
                                </li>
                                <li class="{{ Request::is('stock/out-of-stock') ? 'active' : '' }}">
                                    <a href="{{ route('stock.out-of-stock') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Out of Stock</span>
                                    </a>
                                </li>
                            @endcan
                            @can('access.stocks')
                                <li class="{{ Request::is('stock/transfer') ? 'active' : '' }}">
                                    <a href="{{ route('stock.transfer') }}">
                                        <x-heroicon-o-arrows-right-left class="w-4 h-4" /><span>Stock-Transfer</span>
                                    </a>
                                </li>
                            @elsecan('create.stocks')
                                <li class="{{ Request::is('stock/transfer/create') ? 'active' : '' }}">
                                    <a href="{{ route('stock.transfer.create') }}">
                                        <x-heroicon-o-arrows-right-left class="w-4 h-4" /><span>Add Stock Transfer</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                <hr>

                @canany(['access.customers', 'create.customers'])
                    <li class="{{ Request::is('customers*') ? 'active' : '' }}">
                        @can('access.customers')
                            <a href="{{ route('customers.index') }}" class="svg-icon">
                                <x-heroicon-o-user-group class="w-6 h-6" />
                                <span class="ml-3">Customers</span>
                            </a>
                        @elsecan('create.customers')
                            <a href="{{ route('customers.create') }}" class="svg-icon">
                                <x-heroicon-o-user-group class="w-6 h-6" />
                                <span class="ml-3">Create Customer</span>
                            </a>
                        @endcan
                    </li>
                @endcan

                @canany(['access.suppliers', 'create.suppliers'])
                    <li class="{{ Request::is('suppliers*') ? 'active' : '' }}">
                        @can('access.suppliers')
                            <a href="{{ route('suppliers.index') }}" class="svg-icon">
                                <x-heroicon-o-user-group class="w-6 h-6" />
                                <span class="ml-3">Suppliers</span>
                            </a>
                        @elsecan('create.suppliers')
                            <a href="{{ route('suppliers.create') }}" class="svg-icon">
                                <x-heroicon-o-user-group class="w-6 h-6" />
                                <span class="ml-3">Create Supplier</span>
                            </a>
                        @endcan
                    </li>
                @endcan

                @canany(['access.stocks', 'create.stocks'])
                    <li class="{{ Request::is('expenses*') ? 'active' : '' }}">
                        @can('access.stocks')
                            <a href="{{ route('expenses.index') }}" class="svg-icon">
                                <x-heroicon-o-receipt-percent class="w-6 h-6" />
                                <span class="ml-3">Expenses</span>
                            </a>
                        @elsecan('create.stocks')
                            <a href="{{ route('expenses.create') }}" class="svg-icon">
                                <x-heroicon-o-receipt-percent class="w-6 h-6" />
                                <span class="ml-3">Create Expense</span>
                            </a>
                        @endcan
                    </li>
                    <li class="{{ Request::is('branches*') ? 'active' : '' }}">
                        @can('access.stocks')
                            <a href="{{ route('branches.index') }}" class="svg-icon">
                                <x-heroicon-o-building-office-2 class="w-6 h-6" />
                                <span class="ml-3">Branches</span>
                            </a>
                        @elsecan('create.stocks')
                            <a href="{{ route('branches.create') }}" class="svg-icon">
                                <x-heroicon-o-building-office-2 class="w-6 h-6" />
                                <span class="ml-3">Create Branch</span>
                            </a>
                        @endcan
                    </li>
                @endcan

                <hr>
                @can('access.roles')
                    <li>
                        <a href="#permission" class="collapsed" data-toggle="collapse" aria-expanded="false">
                            <x-heroicon-o-key class="w-6 h-6" />
                            <span class="ml-3">Role & Permission</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="permission" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                            <li class="{{ Request::is('permission') ? 'active' : '' }}">
                                <a href="{{ route('permission.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Permissions</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('role') ? 'active' : '' }}">
                                <a href="{{ route('role.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Roles</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('role/permission*') ? 'active' : '' }}">
                                <a href="{{ route('rolePermission.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Role in Permissions</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="{{ Request::is('settings*') ? 'active' : '' }}">
                        <a href="{{ route('settings.index') }}" class="svg-icon">
                            <x-heroicon-o-cog-6-tooth class="w-6 h-6" />
                            <span class="ml-3">Settings</span>
                        </a>
                    </li>
                @endcan

                @canany(['access.users', 'create.users'])
                    <li class="{{ Request::is('users*') ? 'active' : '' }}">
                        @can('access.users')
                            <a href="{{ route('users.index') }}" class="svg-icon">
                                <x-heroicon-o-users class="w-6 h-6" />
                                <span class="ml-3">Users</span>
                            </a>
                        @elsecan('create.users')
                            <a href="{{ route('users.create') }}" class="svg-icon">
                                <x-heroicon-o-users class="w-6 h-6" />
                                <span class="ml-3">Create User</span>
                            </a>
                        @endcan
                    </li>
                @endcan

                @canany(['access.payments', 'create.payments'])
                    <li class="{{ Request::is('payments*') ? 'active' : '' }}">
                        @can('access.payments')
                            <a href="{{ route('payments.index') }}" class="svg-icon">
                                <x-heroicon-o-wallet class="w-6 h-6"
                                    style="width: 24px; height: 24px; min-width: 24px; flex-shrink: 0;" />
                                <span class="ml-3">Wallet/Branchless Banking</span>
                            </a>
                        @elsecan('create.payments')
                            <a href="{{ route('payments.create') }}" class="svg-icon">
                                <x-heroicon-o-wallet class="w-6 h-6"
                                    style="width: 24px; height: 24px; min-width: 24px; flex-shrink: 0;" />
                                <span class="ml-3">Create Payment</span>
                            </a>
                        @endcan
                    </li>
                @endcan

                {{-- @can('database.menu')
                    <li class="{{ Request::is('database/backup*') ? 'active' : '' }}">
                        <a href="{{ route('backup.index') }}" class="svg-icon">
                            <x-heroicon-o-circle-stack class="w-6 h-6" />
                            <span class="ml-3">Backup Database</span>
                        </a>
                    </li>
                @endcan --}}
            </ul>
        </nav>
        <div class="p-3"></div>
    </div>
</div>
