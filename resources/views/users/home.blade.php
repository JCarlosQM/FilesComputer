@extends('users.index')

@section('title', 'Home')

@section('content')

<div class="container mt-5">

    {{-- Título centrado y con espacio --}}
    <h1 class="text-center mb-4 text-light">Lista de Errores</h1>

    {{-- Mostrar texto de búsqueda si existe --}}
    @if(request('query'))
        <p class="text-center text-info mb-4">
            Resultados para: <strong>{{ request('query') }}</strong>
        </p>
    @endif

    {{-- Validar si hay errores --}}
    @if(isset($errores) && $errores->count() > 0)

        {{-- Grid de 4 cards por fila --}}
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 justify-content-center">

            @foreach($errores as $error)
                <div class="col d-flex">
                    <div class="card shadow-sm flex-fill cursor-pointer"
                         style="cursor: pointer;"
                         data-bs-toggle="modal"
                         data-bs-target="#errorModal"
                         data-imagen="{{ $error->imagen_url }}"
                         data-numero="{{ $error->numero_error }}"
                         data-descripcion="{!! nl2br(e($error->descripcion)) !!}"
                         data-fecha="{{ $error->created_at }}">
                         
                        @if($error->imagen_url)
                            <img src="{{ $error->imagen_url }}" class="card-img-top" alt="Imagen del error">
                        @else
                            {{-- Imagen placeholder si no hay URL --}}
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:180px;">
                                <span>No hay imagen</span>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center mb-3">Error #{{ $error->numero_error }}</h5>
                            <p class="card-text text-center text-truncate" style="flex-grow:1;">
                                {!! \Illuminate\Support\Str::words(
                                    preg_replace(
                                        '/(https?:\/\/[^\s]+)/',
                                        '<a href="$1" target="_blank" style="color:#00ff7f;">$1</a>',
                                        e($error->descripcion)
                                    ),
                                    10,
                                    '...'
                                ) !!}
                            </p>
                            <small class="text-muted text-center mt-auto">{{ \Carbon\Carbon::parse($error->created_at)->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <p class="text-center mt-5 text-white-50 fs-5">No se encontraron errores para la búsqueda <strong>{{ request('query') ?? '' }}</strong>.</p>
    @endif

</div>

<!-- Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h5 class="modal-title text-success fw-bold">Detalle del Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImagen" src="" class="img-fluid mb-3 rounded" alt="Imagen del error" style="max-height:300px; object-fit:contain;">
                <h5 id="modalNumero" class="mb-3"></h5>
                <p id="modalDescripcion" style="white-space: pre-wrap; font-size:1.1rem;"></p>
                <small id="modalFecha" class="text-muted"></small>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Cursor pointer para cards */
    .cursor-pointer {
        cursor: pointer;
        transition: transform 0.15s ease-in-out;
    }
    .cursor-pointer:hover {
        transform: scale(1.03);
        box-shadow: 0 0.5rem 1rem rgba(0, 255, 127, 0.4);
    }
</style>

@endsection
