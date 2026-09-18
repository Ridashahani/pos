<div class="iq-sidebar sidebar-default ">
    <div class="iq-sidebar-logo d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="header-logo">
            <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid rounded-normal light-logo" alt="logo">
            <h5 class="logo-title light-logo ml-3">POSDash</h5>
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

                @if (auth()->user()->can('access.pos'))
                <li class="{{ Request::is('pos*') ? 'active' : '' }}">
                    <a href="{{ route('pos.index') }}" class="svg-icon">
                        <x-heroicon-o-shopping-cart class="w-6 h-6" />
                        <span class="ml-3">POS</span>
                    </a>
                </li>
                @endif



              
                <hr>

                @if (auth()->user()->can('access.products'))
                    @php($productsMenuActive = Request::is('products*') || Request::is('variations*') || Request::is('categories*'))
                    <li class="{{ $productsMenuActive ? 'active' : '' }}">
                        <a href="#products" class="{{ $productsMenuActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $productsMenuActive ? 'true' : 'false' }}">
                            <x-heroicon-o-archive-box class="w-6 h-6" />
                            <span class="ml-3">Products</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                    </a>
                    <ul id="products" class="iq-submenu collapse {{ $productsMenuActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">
                        <li class="{{ Request::is('products') || Request::is('products/*') && !Request::is('products/create') ? 'active' : '' }}">
                            <a href="{{ route('products.index') }}">
                                <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Products</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('variations*') ? 'active' : '' }}">
                            <a href="{{ route('variations.index') }}">
                                <x-heroicon-o-squares-2x2 class="w-4 h-4" /><span>Variations</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('products/create') || Request::is('products/*/edit') ? 'active' : '' }}">
                            <a href="{{ route('products.create') }}">
                                <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Add Product</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('categories*') ? 'active' : '' }}">
                            <a href="{{ route('categories.index') }}">
                                <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Categories</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if (auth()->user()->can('access.sales'))
                    <li>
                        <a href="#orders" class="collapsed" data-toggle="collapse" aria-expanded="{{ Request::is('orders*') || Request::is('pending/due*') ? 'true' : 'false' }}">
                            <x-heroicon-o-shopping-bag class="w-6 h-6" />
                            <span class="ml-3">Sales</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="orders" class="iq-submenu collapse {{ Request::is('orders*') || Request::is('pending/due*') ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">
                            <li class="{{ Request::is('orders/pending*') ? 'active' : '' }}">
                                <a href="{{ route('order.pendingOrders') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Pending Sales</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('orders/complete*') ? 'active' : '' }}">
                                <a href="{{ route('order.completeOrders') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Complete Sales</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('pending/due*') ? 'active' : '' }}">
                                <a href="{{ route('order.pendingDue') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Pending Due</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="#purchases" class="collapsed" data-toggle="collapse" aria-expanded="{{ Request::is('purchases*') ? 'true' : 'false' }}">
                            <x-heroicon-o-archive-box-arrow-down class="w-6 h-6" />
                            <span class="ml-3">Purchases</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="purchases" class="iq-submenu collapse {{ Request::is('purchases*') ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">
                            <li class="{{ Request::is('purchases') ? 'active' : '' }}">
                                <a href="{{ route('purchases.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>All Purchases</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('purchases/create') ? 'active' : '' }}">
                                <a href="{{ route('purchases.create') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Add Purchase</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('purchases/returns') ? 'active' : '' }}">
                                <a href="{{ route('purchases.returns') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Purchase Returns</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

               
  
                <li>
                    <a href="#stock" class="collapsed" data-toggle="collapse" aria-expanded="{{ Request::is('stock*') ? 'true' : 'false' }}">
                        <x-heroicon-o-archive-box-arrow-down class="w-6 h-6" />
                        <span class="ml-3">Stock</span>
                        <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                    </a>
                    <ul id="stock" class="iq-submenu collapse {{ Request::is('stock*') ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">
                        <li class="{{ Request::is('stock/in') ? 'active' : '' }}">
                            <a href="{{ route('stock.in') }}">
                                <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Stock-In</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('stock/out') ? 'active' : '' }}">
                            <a href="{{ route('stock.out') }}">
                                <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Stock-out</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('stock/transfer') ? : '' }}">
                            <a href="{{ route('stock.transfer') }}">
                                <x-heroicon-o-arrows-right-left class="w-4 h-4" /><span>Stock-Transfer</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <hr>

                @if (auth()->user()->can('access.customers'))
w                <li class="{{ Request::is('customers*') ? 'active' : '' }}">
                    <a href="{{ route('customers.index') }}" class="svg-icon">
                        <x-heroicon-o-user-group class="w-6 h-6" />
                        <span class="ml-3">Customers</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->can('access.suppliers'))
                <li class="{{ Request::is('suppliers*') ? 'active' : '' }}">
                    <a href="{{ route('suppliers.index') }}" class="svg-icon">
                        <x-heroicon-o-user-group class="w-6 h-6" />
                        <span class="ml-3">Suppliers</span>
                    </a>
                </li>
                @endif

                <hr>


            @if (auth()->user()->can('access.roles'))
            <li>
                <a href="#permission" class="collapsed" data-toggle="collapse" aria-expanded="false">
                    <x-heroicon-o-key class="w-6 h-6" />
                    <span class="ml-3">Role & Permission</span>
                    <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                </a>
                <ul id="permission" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                    <li class="{{ Request::is(['permission', 'permission/create', 'permission/edit/*']) ? 'active' : '' }}">
                        <a href="{{ route('permission.index') }}">
                            <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Permissions</span>
                        </a>
                    </li>
                    <li class="{{ Request::is(['role', 'role/create', 'role/edit/*']) ? 'active' : '' }}">
                        <a href="{{ route('role.index') }}">
                            <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Roles</span>
                        </a>
                    </li>
                    <li class="{{ Request::is(['role/permission*']) ? 'active' : '' }}">
                        <a href="{{ route('rolePermission.index') }}">
                            <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Role in Permissions</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if (auth()->user()->can('access.users'))
            <li class="{{ Request::is('users*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="svg-icon">
                    <x-heroicon-o-users class="w-6 h-6" />
                    <span class="ml-3">Users</span>
                </a>
            </li>
            @endif
            @if (auth()->user()->can('access.stocks'))
            <li class="{{ Request::is('expenses*') ? 'active' : '' }}">
                <a href="{{ route('expenses.index') }}" class="svg-icon">
                    <x-heroicon-o-receipt-percent class="w-6 h-6" />
                    <span class="ml-3">Expenses</span>
                </a>
            </li>
            <li class="{{ Request::is('branches*') ? 'active' : '' }}">
                <a href="{{ route('branches.index') }}" class="svg-icon">
                    <x-heroicon-o-building-office-2 class="w-6 h-6" />
                    <span class="ml-3">Branches</span>
                </a>
            </li>
            @endif

            @if (auth()->user()->can('database.menu'))
            {{-- <li class="{{ Request::is('database/backup*') ? 'active' : '' }}">
            <a href="{{ route('backup.index') }}" class="svg-icon">
                <x-heroicon-o-circle-stack class="w-6 h-6" />
                <span class="ml-3">Backup Database</span>
            </a>
            </li> --}}
            @endif

            {{-- <li class="{{ Request::is('help*') ? 'active' : '' }}">
            <a href="{{ route('help.index') }}" class="svg-icon">
                <x-heroicon-o-question-mark-circle class="w-6 h-6" />
                <span class="ml-3">Help</span>
            </a>
            </li> --}}
            </ul>
        </nav>
        <div class="p-3"></div>
    </div>
</div>