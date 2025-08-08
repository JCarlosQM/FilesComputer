@extends('layouts.app')

@section('title', 'Gestión de Errores de Código')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Gestión de Errores de Código</h2>
        <button class="btn btn-success" onclick="abrirModalCrear()">
            <i class="bi bi-plus-lg me-1"></i> Crear Error
        </button>
    </div>

    <table id="tabla-errores" class="display" style="width:100%">
        <thead>
            <tr>
                <th>N°</th>
                <th>Número Error</th>
                <th>Descripción</th>
                <th>Imagen</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Modal Crear Error -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="createForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Error de Código</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="numeroError" class="form-label">Número de Error</label>
                            <input type="text" id="numeroError" name="numero_error" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen referencial</label>
                            <input type="file" id="imagen" name="imagen" accept="image/*" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Crear</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/funciones.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    inicializarDataTableErrores();
});
</script>
@endpush
