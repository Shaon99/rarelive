<nav class="navbar navbar-expand-lg main-navbar {{ $hideNavSidebar??'' }}">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav">
            <li>
                <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a>
            </li>
            <li class="mt-1 text-center text-dark d-lg-block d-xl-block d-none">
                <small class="time" id="current-time"></small>
            </li>
        </ul>
    </div>
    <ul class="navbar-nav navbar-right">
        {{-- @if (auth()->guard('admin')->user()->can('recent_order'))
        <li class="mx-1 my-auto nav-item dropdown no-arrow d-lg-block d-xl-block d-none">
            <button type="button" class="px-4 btn btn-custom-pos recent-order"> <i class="fas fa-solid fa-bars-staggered mr-1"></i> {{ __('Recent Order') }}</button>
        </li>
        @endif --}}
        {{-- @if (auth()->guard('admin')->user()->can('daily_report'))
        <li class="mx-1 my-auto nav-item dropdown no-arrow d-lg-block d-xl-block d-none">
            <button type="button" class="px-4 btn btn-custom-pos daily-report"> <i class="fas fa-chart-line"></i> {{ __('Daily Report') }}</button>
        </li>
        @endif --}}
        {{-- sales list --}}
        @if (auth()->guard('admin')->user()->can('order_list'))
            <li class="mx-1 my-auto nav-item dropdown no-arrow d-lg-block d-lg-block d-xl-block d-none">
                <a href="{{ route('admin.sales.index', ['system_status' => 'draft']) }}"
                    class="px-4 btn btn-custom-pos"> <i class="fas fa-solid fa-bars-staggered mr-1"
                        aria-hidden="true"></i><span>{{ __('Draft') }} <span
                            class="badge badge-warning mt-0 draftCount">{{ $draftSales }}</span></a>
            </li>
        @endif
        @if (auth()->guard('admin')->user()->can('order_list'))
            <li class="mx-1 my-auto nav-item dropdown no-arrow d-lg-block d-xl-block d-none">
                <a href="{{ route('admin.sales.index') }}" class="px-4 btn btn-custom-pos"> <i
                        class="fas fa-solid fa-bars-staggered mr-1" aria-hidden="true"></i> {{ __('Orders') }}</a>
            </li>
        @endif
        @if (auth()->guard('admin')->user()->can('order_add'))
            <li class="mx-1 my-auto nav-item dropdown no-arrow">
                <a href="{{ route('admin.sales.create') }}" class="px-lg-4 px-sm-2 btn btn-custom-pos"> <i
                        class="fa fa-shopping-bag mr-1" aria-hidden="true"></i> {{ __('POS') }}</a>
            </li>
        @endif

        <li class="mx-1 my-auto nav-item dropdown no-arrow">
            <div class="language-switcher">
                <div class="dropdown">
                    <button class="btn btn-custom-pos dropdown-toggle" type="button" id="languageDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ session('locale') ?? 'EN' }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right lanDropDown" aria-labelledby="languageDropdown">
                        @foreach ($language_top as $top)
                            <a class="dropdown-item language-dropdown {{ session('locale') == $top->short_code ? 'active' : '' }}"
                                href="#" data-lang="{{ $top->short_code }}">
                                {{ __(ucwords($top->short_code)) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </li>

        <li class="dropdown dropdown-list-toggle pl-2"><a href="#" id="mark-all-read"
                class="nav-link notification-toggle nav-link-lg" data-toggle="dropdown">
                <i class="far fa-bell"></i>
                @if ($unreadCount)
                    <span class="badge badge-danger navbar-badge">{{ $unreadCount }}</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-list dropdown-menu-right">
                <div class="dropdown-header">{{ __('Notifications') }}
                    <div class="float-right">
                        <a href="{{ route('admin.markNotification') }}"
                            class="text-dark">{{ __('Mark All As Read') }}</a>
                    </div>
                </div>
                <div class="dropdown-list-content dropdown-list-icons">
                    @forelse($notifications as $notification)
                        <a href="@if (!empty($notification->data['link'])) {{ $notification->data['link'] }} @else {{ route('admin.notifications.markAsRead', $notification->id) }} @endif"
                            @if (!empty($notification->data['link'])) target="_blank" @endif
                            class="dropdown-item {{ $notification->read_at ? '' : 'dropdown-item-unread' }}">
                            <div
                                class="dropdown-item-icon {{ $notification->read_at ? 'bg-primary' : 'bg-warning' }} text-white">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="dropdown-item-desc">
                                <span class="{{ !$notification->read_at ? 'font-weight-bold' : '' }}">
                                    {{ $notification->data['message'] }}
                                </span>
                                <div class="time {{ !$notification->read_at ? 'text-warning' : 'text-primary' }}">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>

                                {{-- Optional: extra data --}}
                                @if (isset($notification->data['successful_imports']))
                                    <p class="{{ !$notification->read_at ? 'font-weight-bold' : '' }}">
                                        <strong>Successful Imports:</strong>
                                        {{ $notification->data['successful_imports'] }}
                                    </p>
                                    <p class="{{ !$notification->read_at ? 'font-weight-bold' : '' }}">
                                        <strong>Failed Imports:</strong> {{ $notification->data['failed_imports'] }}
                                    </p>
                                    @if (!empty($notification->data['failed_product_codes']))
                                        <p class="{{ !$notification->read_at ? 'font-weight-bold' : '' }}">
                                            <strong>Failed Products:</strong>
                                        </p>
                                        <ul>
                                            @foreach ($notification->data['failed_product_codes'] as $product)
                                                <li class="{{ !$notification->read_at ? 'font-weight-bold' : '' }}">
                                                    {{ $product['name'] }} ({{ $product['code'] }})
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="text-center notification-p">{{ __('There are no new notifications') }}</p>
                    @endforelse
                </div>
                <div class="dropdown-footer text-center">
                    <a href="{{ route('admin.notifications.index') }}"
                        class="text-dark">{{ __('View All Notifications') }}</a>
                </div>
            </div>
        </li>

        <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link nav-link-lg nav-link-user pl-4">
                @if (auth()->guard('admin')->user()->image)
                    <img alt="image" src={{ getFile('admin', auth()->guard('admin')->user()->image) }}
                        class="rounded-circle border">
                @else
                    <img alt="image" src={{ getFile('default', @$general->default_image) }} class="rounded-circle">
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('admin.profile') }}" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> {{ __('Profile') }}
                </a>
                <a href="{{ route('admin.logout') }}" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                </a>
            </div>
        </li>
    </ul>
</nav>
