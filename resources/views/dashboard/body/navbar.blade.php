<div class="iq-top-navbar">
    <div class="iq-navbar-custom">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <div class="iq-navbar-logo d-flex align-items-center justify-content-between">
                <div class="iq-menu-bt-sidebar">
                    <x-heroicon-o-bars-3 class="wrapper-menu w-8 h-8" />
                </div>
                <a href="{{ route('dashboard') }}" class="header-logo">
                    <img src="../assets/images/logo.png" class="img-fluid rounded-normal" alt="logo">
                    <h5 class="logo-title ml-3">POSDash</h5>
                </a>
            </div>
            <div class="iq-search-bar device-search">
                <form action="#" class="searchbox">
                    <a class="search-link" href="#">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    </a>
                    <input type="text" class="text search-input" placeholder="Search here...">
                </form>
            </div>
            <div class="d-flex align-items-center">
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-label="Toggle navigation">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-list align-items-center">
                        <li class="nav-item nav-icon search-content">
                            <a href="#" class="search-toggle rounded" id="dropdownSearch" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            </a>
                            <div class="iq-search-bar iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownSearch">
                                <form action="#" class="searchbox p-2">
                                    <div class="form-group mb-0 position-relative">
                                        <input type="text" class="text search-input font-size-12"
                                            placeholder="type here to search...">
                                        <a href="#" class="search-link">
                                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </li>
                        @if (auth()->check() && !auth()->user()->isAdmin() && auth()->user()->activeBranch)
                            @php $branchCount = auth()->user()->branches()->count(); @endphp
                            <li class="nav-item mr-2">
                                @if ($branchCount > 1)
                                    {{-- Multiple branches: show name + Switch button --}}
                                    <a href="{{ route('branch.select') }}"
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                        style="border-radius:20px; font-weight:600; padding:2px 10px; height:30px; font-size:12px;"
                                        title="Click to switch active branch">
                                        <svg class="mr-1" style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span>{{ auth()->user()->activeBranch->name }}</span>
                                        <span class="badge badge-primary text-white ml-2" style="font-size:9px;">Switch</span>
                                    </a>
                                @else
                                    {{-- Single branch: just show the name, no switch --}}
                                    <span class="d-inline-flex align-items-center px-2"
                                        style="border-radius:20px; font-weight:600; font-size:12px; color:#3b82f6; background:rgba(59,130,246,.08); height:30px;">
                                        <svg class="mr-1" style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        {{ auth()->user()->activeBranch->name }}
                                    </span>
                                @endif
                            </li>
                        @endif
                        <li class="nav-item nav-icon dropdown caption-content">
                            <a href="#" class="search-toggle dropdown-toggle" id="dropdownMenuButton4"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ auth()->user()->photo ? asset('storage/profile/' . auth()->user()->photo) : asset('assets/images/user/1.png') }}"
                                    class="img-fluid rounded" alt="user">
                            </a>
                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-0 text-center">
                                        <div class="media-body profile-detail text-center">
                                            <img src="{{ asset('assets/images/page-img/profile-bg.jpg') }}"
                                                alt="profile-bg" class="rounded-top img-fluid mb-4">
                                            <img src="{{ auth()->user()->photo ? asset('storage/profile/' . auth()->user()->photo) : asset('assets/images/user/1.png') }}"
                                                alt="profile-img" class="rounded profile-img img-fluid avatar-70">
                                        </div>
                                        <div class="p-3">
                                            <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                                            @if (!auth()->user()->isAdmin() && auth()->user()->activeBranch)
                                                <p class="mb-0 text-muted font-size-12">
                                                    Branch: <strong>{{ auth()->user()->activeBranch->name }}</strong>
                                                </p>
                                            @endif
                                            <p class="mb-0">Since
                                                {{ date('d M, Y', strtotime(auth()->user()->created_at)) }}</p>
                                            <div class="d-flex align-items-center justify-content-center mt-3">
                                                <a href="{{ route('profile') }}" class="btn border mr-2">Profile</a>
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn border">Sign Out</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>
