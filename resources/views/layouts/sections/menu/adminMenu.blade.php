<aside id="layout-menu" class="layout-menu menu-vertical menu synex-admin-sidebar">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros', ['variant' => 'dore'])</span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">SYNEX</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>
    <div class="synex-admin-sidebar-caption">Administration</div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Pilotage</span></li>
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-grid-alt"></i><div>Tableau de bord</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase"><span class="menu-header-text">Observations</span></li>
        <li class="menu-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
            <a href="{{ route('admin.events.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-calendar-event"></i><div>Observations et incidents</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.references.*') ? 'active' : '' }}">
            <a href="{{ route('admin.references.index', 'actors') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-group"></i><div>Acteurs</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.references.index', 'provinces') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-map"></i><div>Territoires</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.references.index', 'themes') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-purchase-tag"></i><div>Thématiques</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.references.index', 'event-types') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-category"></i><div>Types d’événements</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.references.index', 'sub-events') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-git-branch"></i><div>Sous-types et événements connexes</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase"><span class="menu-header-text">Programmes et services</span></li>
        <li class="menu-item {{ request()->routeIs('admin.program-news.*') ? 'active' : '' }}">
            <a href="{{ route('admin.program-news.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-news"></i><div>Actualités</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.prevention.*') && request()->query('section') !== 'initiatives' ? 'active' : '' }}">
            <a href="{{ route('admin.prevention.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-shield-quarter"></i><div>Prévention</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.prevention.*') && request()->query('section') === 'initiatives' ? 'active' : '' }}">
            <a href="{{ route('admin.prevention.index', ['section' => 'initiatives']) }}#prevention-initiatives" class="menu-link">
                <i class="menu-icon icon-base bx bx-git-merge"></i><div>Mécanismes de résolution</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.care.*') ? 'active' : '' }}">
            <a href="{{ route('admin.care.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-first-aid"></i><div>Prise en charge</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('admin.prevention.index') }}#prevention-resources" class="menu-link">
                <i class="menu-icon icon-base bx bx-book-open"></i><div>Ressources</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.veille.*') ? 'active' : '' }}">
            <a href="{{ route('admin.veille.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-slider-alt"></i><div>Contenu de la veille</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase"><span class="menu-header-text">Analyses et administration</span></li>
        <li class="menu-item">
            <a href="{{ route('dashboard-analytics-two') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-bar-chart-alt-2"></i><div>Analyses et rapports</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
            <a href="{{ route('admin.import.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-transfer"></i><div>Import de données CSV</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}">
            <a href="{{ route('admin.contact.index') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-envelope"></i><div>Messages de contact</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.create') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-user-plus"></i><div>Comptes et rôles</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.site.edit') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-cog"></i><div>Administration du site</div>
            </a>
        </li>
    </ul>
</aside>
