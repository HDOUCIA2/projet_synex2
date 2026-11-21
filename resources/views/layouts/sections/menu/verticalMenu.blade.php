@php
use Illuminate\Support\Facades\Route;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    <div class="app-brand demo">
        <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros', ['variant' => 'blanc'])</span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">{{ $siteSettings->name }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)

        {{-- adding active and open class if child is active --}}

        {{-- menu headers --}}
        @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
        @else

        {{-- active menu method --}}
        @php
        $activeClass = null;
        $currentRouteName = Route::currentRouteName();

        if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
        }
        elseif (isset($menu->submenu)) {
        if (gettype($menu->slug) === 'array') {
        foreach($menu->slug as $slug){
        if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
        $activeClass = 'active open';
        }
        }
        }
        else{
        if (str_contains($currentRouteName,$menu->slug) and strpos($currentRouteName,$menu->slug) === 0) {
        $activeClass = 'active open';
        }
        }
        }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{$activeClass}}">
            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                @isset($menu->icon)
                <i class="{{ $menu->icon }}"></i>
                @endisset
                <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                @isset($menu->badge)
                <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">{{ $menu->badge[1] }}</div>
                @endisset
            </a>

            {{-- submenu --}}
            @isset($menu->submenu)
            @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
            @endisset
        </li>
        @endif
        @endforeach

        @auth
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Administration</span></li>
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.events.') ? 'active' : '' }}">
            <a href="{{ route('admin.events.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-calendar-event"></i>
                <div>Événements</div>
            </a>
        </li>
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.import.') ? 'active' : '' }}">
            <a href="{{ route('admin.import.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-upload"></i>
                <div>Import CSV</div>
            </a>
        </li>
        @if (auth()->user()->isAdmin())
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.references.') ? 'active' : '' }}">
            <a href="{{ route('admin.references.index', 'provinces') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-list-ul"></i>
                <div>Référentiels</div>
            </a>
        </li>
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.veille.') ? 'active' : '' }}">
            <a href="{{ route('admin.veille.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-slider-alt"></i>
                <div>Contenu de la veille</div>
            </a>
        </li>
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.program-news.') ? 'active' : '' }}">
            <a href="{{ route('admin.program-news.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-news"></i>
                <div>Actualités du programme</div>
            </a>
        </li>
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.users.') ? 'active' : '' }}">
            <a href="{{ route('admin.users.create') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-user-plus"></i>
                <div>Comptes</div>
            </a>
        </li>
        <li class="menu-item {{ str_starts_with($currentRouteName, 'admin.settings.') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.site.edit') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-cog"></i>
                <div>Réglages du site</div>
            </a>
        </li>
        @endif
        <li class="menu-item {{ $currentRouteName === 'pages-account-settings-account' ? 'active' : '' }}">
            <a href="{{ route('pages-account-settings-account') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-user"></i>
                <div>Mon profil</div>
            </a>
        </li>
        @endauth
    </ul>

</aside>
