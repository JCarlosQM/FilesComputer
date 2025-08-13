<nav class="navbar navbar-expand-md aspectIndep shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">ErrorCode</a>

    <!-- Botón hamburguesa solo visible en dispositivos < md -->
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
      aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido colapsable en offcanvas solo en pantallas pequeñas -->
    <div class="offcanvas offcanvas-end d-md-none" tabindex="-1" id="offcanvasNavbar"
      aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
      </div>
      <div class="offcanvas-body">
        <form class="d-flex" role="search" method="GET" action="{{ route('buscar') }}">
          <input name="query" class="form-control me-2" type="search" placeholder="Buscar" aria-label="Buscar" />
          <button class="btn btn-outline-success" type="submit">Buscar</button>
        </form>
      </div>
    </div>

    <!-- Menú expandido en pantallas medianas o mayores -->
    <div class="collapse navbar-collapse d-none d-md-flex justify-content-end p-2 m-2">
      <form class="d-flex" role="search" method="GET" action="{{ route('buscar') }}">
        <input name="query" class="form-control me-2" type="search" placeholder="Buscar" aria-label="Buscar" />
        <button class="btn btn-outline-success" type="submit">Buscar</button>
      </form>
    </div>
  </div>
</nav>
