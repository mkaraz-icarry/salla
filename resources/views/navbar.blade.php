<nav class="navbar navbar-expand-lg navbar-light bg-light">

    <a class="navbar-brand" href="/">
        <img class="navbar__logo" src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('imgs/iCARRY_LOGO.png') : secure_asset('imgs/iCARRY_LOGO.png') }}" />
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        @if (!isset($merchant_store_id))
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/api/redirect">Install</a>
                </li>
            </ul>
        @else
            <ul class="navbar-nav ml-auto">
                <li class="nav-item {{ Request::is('settings') ? 'active' : '' }}">
                    <a class="nav-link" href="/settings">Settings</a>
                </li>
                {{-- <li class="nav-item {{ Request::is('webhook/list') ? 'active' : '' }}">
                    <a class="nav-link" href="/webhook/list">webhook list</a>
                </li> --}}
                {{-- <li class="nav-item {{ Request::is('orders') ? 'active' : '' }}">
                    <a class="nav-link" href="/orders">Orders</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link" href="/api/refresh-token">Refresh Token</a>
                </li>
            </ul>
        @endif
    </div>
</nav>
