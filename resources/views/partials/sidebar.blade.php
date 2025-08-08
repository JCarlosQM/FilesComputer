<div class="d-flex flex-column p-3 min-vh-100 shadow-sm" style="width: 100%;">
    <ul class="nav nav-pills flex-column gap-2">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" 
               class="nav-link d-flex align-items-center gap-2 sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 fs-5"></i> 
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('usuarios.index') }}" 
               class="nav-link d-flex align-items-center gap-2 sidebar-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-person fs-5"></i> 
                <span>Usuarios</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('errores.index') }}" 
               class="nav-link d-flex align-items-center gap-2 sidebar-link {{ request()->routeIs('errores.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-octagon fs-5"></i> 
                <span>Errores</span>
            </a>
        </li>
    </ul>
</div>
