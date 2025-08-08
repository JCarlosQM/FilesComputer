<nav class="navbar navbar-expand-lg navbar-dark backHeader">
  <div class="container-fluid">

    <!-- Botón toggle sidebar para móviles -->
    <button class="btn btn-sm btn-light me-2 d-md-none" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
      <i class="bi bi-list"></i>
    </button>

    <!-- Marca -->
    <a class="navbar-brand mb-2" href="/dashboard">Mi Panel</a>

    <!-- Botón colapsable navbar -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido" aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido colapsable -->
    <div class="collapse navbar-collapse" id="navbarContenido">
      <ul class="navbar-nav ms-auto align-items-center">

        <!-- Dropdown para seleccionar tema -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Tema
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item theme-select" href="#" data-theme="light">Claro</a></li>
            <li><a class="dropdown-item theme-select" href="#" data-theme="dark">Oscuro</a></li>
            <li><a class="dropdown-item theme-select" href="#" data-theme="blue">Azul</a></li>
          </ul>
        </li>

        <!-- Nombre del usuario logueado -->
        @php
            $usuario = session('usuario');
        @endphp

        @if ($usuario)
            <li class="nav-item nav-link">
                Hola, {{ $usuario['nombre'] ?? 'Usuario' }}
            </li>
        @else
            <li class="nav-item nav-link text-white">
                Invitado
            </li>
        @endif


        <!-- Link para cerrar sesión -->
        <li class="nav-item">
          <a class="nav-link" href="/logout">Cerrar sesión</a>
        </li>

      </ul>
    </div>

  </div>
</nav>
