<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{url('/')}}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
        <span class="app-brand-text demo menu-text fw-bold text-heading">{{ $siteSettings->name }}</span>
    </a>
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base bx bx-menu icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    @if (auth()->check() && auth()->user()->isAdmin())
    <div class="navbar-nav align-items-center synex-admin-topbar">
        <div>
            <strong>Tableau de bord administrateur</strong>
            <small>Gestion des données, du contenu et du paramétrage de SYNEX</small>
        </div>
    </div>
    @else
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="icon-base bx bx-search icon-md"></i>
            <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search..." aria-label="Search...">
        </div>
    </div>
    @endif
    <!-- /Search -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        @auth
        @if (auth()->user()->isAdmin())
        <li class="nav-item dropdown synex-admin-notification-dropdown">
            <a class="nav-link dropdown-toggle hide-arrow synex-admin-notification-toggle" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications : {{ $adminNotificationCount }} élément{{ $adminNotificationCount === 1 ? '' : 's' }} à traiter" title="Notifications">
                <i class="icon-base bx bx-bell icon-md"></i>
                @if ($adminNotificationCount > 0)
                    <span class="synex-admin-notification-count">{{ $adminNotificationCount > 99 ? '99+' : $adminNotificationCount }}</span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end synex-admin-notification-menu">
                <li class="synex-admin-notification-heading">
                    <strong>Notifications</strong>
                    <span>{{ $adminNotificationCount }} élément{{ $adminNotificationCount === 1 ? '' : 's' }} à traiter</span>
                </li>
                @forelse ($adminNotifications as $notification)
                    <li>
                        <a class="synex-admin-notification-item" href="{{ $notification['url'] }}">
                            <span class="synex-admin-notification-icon synex-admin-notification-{{ $notification['kind'] }}">
                                <i class="bx {{ $notification['icon'] }}"></i>
                            </span>
                            <span class="synex-admin-notification-content">
                                <strong>{{ $notification['title'] }}</strong>
                                <span>{{ $notification['detail'] }}</span>
                                <small>{{ $notification['date']?->format('d/m/Y H:i') }}</small>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="synex-admin-notification-empty">Aucune notification nécessitant une action.</li>
                @endforelse
                <li class="synex-admin-notification-footer">
                    <a href="{{ route('admin.contact.index') }}">Messages ({{ $unreadContactCount }})</a>
                    <a href="{{ route('admin.events.index', ['status' => 'pending']) }}">À valider ({{ $pendingEventCount }})</a>
                </li>
            </ul>
        </li>
        <li class="nav-item synex-admin-help">
            <a class="nav-link" href="{{ route('admin.dashboard') }}#synex-admin-quick-actions" aria-label="Aide et actions rapides" title="Aide et actions rapides">
                <i class="icon-base bx bx-help-circle icon-md"></i>
            </a>
        </li>
        @endif
        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0 {{ auth()->user()->isAdmin() ? 'synex-admin-user-toggle' : '' }}" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ auth()->user()->avatar_path ? asset('storage/'.auth()->user()->avatar_path) : asset('assets/img/avatars/1.png') }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle">
                </div>
                @if (auth()->user()->isAdmin())
                    <span class="synex-admin-user-meta">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>Administrateur</small>
                    </span>
                    <i class="icon-base bx bx-chevron-down synex-admin-user-chevron" aria-hidden="true"></i>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('pages-account-settings-account') }}">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ auth()->user()->avatar_path ? asset('storage/'.auth()->user()->avatar_path) : asset('assets/img/avatars/1.png') }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                <small class="text-muted">
                                    {{ auth()->user()->isAdmin() ? 'Administrateur' : 'Utilisateur' }}
                                </small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('pages-account-settings-account') }}">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>Mon profil</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.events.index') }}">
                        <i class="icon-base bx bx-calendar-event icon-md me-3"></i><span>Gérer les événements</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.import.index') }}">
                        <i class="icon-base bx bx-upload icon-md me-3"></i><span>Importer un CSV</span>
                    </a>
                </li>
                @if (auth()->user()->isAdmin())
                <li>
                    <a class="dropdown-item" href="{{ route('admin.users.create') }}">
                        <i class="icon-base bx bx-user-plus icon-md me-3"></i><span>Créer un compte</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.references.index', 'provinces') }}">
                        <i class="icon-base bx bx-list-ul icon-md me-3"></i><span>Référentiels</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.settings.site.edit') }}">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Paramètres du site</span>
                    </a>
                </li>
                @endif
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">
                            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
        <!--/ User -->
        @else
        <li class="nav-item">
            <a class="btn btn-primary" href="{{ route('auth-login-basic') }}">Connexion</a>
        </li>
        @endauth
    </ul>
</div>