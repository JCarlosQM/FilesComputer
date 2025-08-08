let tablaUsuarios, tablaErrores;


function getCSRF() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}


// SWET ALERT 

async function confirmarAccion(mensaje = "¿Estás seguro?") {
    const result = await Swal.fire({
        title: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar',
    });
    return result.isConfirmed;
}

async function mostrarAlerta(titulo, texto = '', icono = 'info') {
    await Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        confirmButtonText: 'OK'
    });
}


// INVOCACION  DE API

async function realizarPeticion(url, metodo = 'GET', data = null) {
    const opciones = {
        method: metodo,
        headers: {
            'X-CSRF-TOKEN': getCSRF()
        }
    };

    if (data) {
        opciones.headers['Content-Type'] = 'application/json';
        opciones.body = JSON.stringify(data);
    }

    const res = await fetch(url, opciones);
    if (!res.ok) {
        let mensajeError = `Error: ${res.status} ${res.statusText}`;
        try {
            const json = await res.json();
            if (json.message) mensajeError = json.message;
        } catch { }
        throw new Error(mensajeError);
    }
    return res;
}

function inicializarDataTable() {
    // Destruye la tabla si ya está inicializada para evitar error "Cannot reinitialise"
    if ($.fn.DataTable.isDataTable('#tabla-usuarios')) {
        $('#tabla-usuarios').DataTable().clear().destroy();
    }

    tablaUsuarios = $('#tabla-usuarios').DataTable({
        ajax: {
            url: '/api/usuarios',
            dataSrc: '',
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 }, // índice
            { data: 'nombre' },
            { data: 'email' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    // Escapar comillas simples para evitar romper el onclick
                    const nombre = row.nombre.replace(/'/g, "\\'");
                    const email = row.email.replace(/'/g, "\\'");
                    return `
                        <button class="btn btn-sm btn-primary me-1" onclick="abrirModalUsuario(${data}, '${nombre}', '${email}')">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${data})">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        paging: true,
        pageLength: 10,
        lengthChange: false,
        searching: true,
    });
}

function recargarTabla() {
    if (tablaUsuarios) {
        tablaUsuarios.ajax.reload(null, false);
    }
}


function inicializarDataTableErrores() {
    if ($.fn.DataTable.isDataTable('#tabla-errores')) {
        $('#tabla-errores').DataTable().clear().destroy();
    }
    tablaErrores = $('#tabla-errores').DataTable({
        ajax: {
            url: '/api/errores',
            dataSrc: ''
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'numero_error' },
            { data: 'descripcion' },
            { data: 'imagen_url', render: (data) => data ? `<img src="${data}" style="max-height:60px;">` : 'No hay' },
            { data: 'created_at' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: (data) => `
                    <button class="btn btn-sm btn-danger" onclick="eliminarError(${data})">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                `
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        paging: true,
        pageLength: 10,
        lengthChange: false,
        searching: true
    });
}

function recargarTablaErrores() {
    if (tablaErrores) {
        tablaErrores.ajax.reload(null, false);
    }
}


function abrirModalCrear() {
    // Limpiar campos del formulario crear
    document.getElementById("createNombre").value = '';
    document.getElementById("createEmail").value = '';
    document.getElementById("createPassword").value = '';

    // Mostrar modal crear
    new bootstrap.Modal(document.getElementById('createModal')).show();
}

function abrirModalUsuario(id, nombre, email) {
    document.getElementById("editId").value = id;
    document.getElementById("editNombre").value = nombre;
    document.getElementById("editEmail").value = email;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

async function crearUsuario() {
    const nombre = document.getElementById("createNombre").value;
    const email = document.getElementById("createEmail").value;
    const password = document.getElementById("createPassword").value;

    try {
        await realizarPeticion('/usuarios', 'POST', { nombre, email, password });
        bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
        await mostrarAlerta('¡Creado!', 'El usuario fue creado correctamente.', 'success');
        recargarTabla();
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
}

async function actualizarUsuario() {
    const confirmado = await confirmarAccion("¿Seguro que quieres actualizar este usuario?");
    if (!confirmado) return;

    const id = document.getElementById("editId").value;
    const nombre = document.getElementById("editNombre").value;
    const email = document.getElementById("editEmail").value;

    try {
        await realizarPeticion(`/usuarios/${id}`, 'PUT', { nombre, email });
        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
        await mostrarAlerta('¡Actualizado!', 'El usuario fue actualizado correctamente.', 'success');
        recargarTabla();
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
}

async function eliminarUsuario(id) {
    const confirmado = await confirmarAccion("¿Seguro que deseas eliminar este usuario?");
    if (!confirmado) return;

    try {
        await realizarPeticion(`/usuarios/${id}`, 'DELETE');
        await mostrarAlerta('¡Eliminado!', 'El usuario fue eliminado.', 'success');
        recargarTabla();
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    inicializarDataTable();

    document.getElementById("createForm").addEventListener("submit", function (e) {
        e.preventDefault();
        crearUsuario();
    });

    document.getElementById("editForm").addEventListener("submit", function (e) {
        e.preventDefault();
        actualizarUsuario();
    });
});


// gestion de errores 


function abrirModalCrear() {
    document.getElementById('numeroError').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('imagen').value = '';
    new bootstrap.Modal(document.getElementById('createModal')).show();
}

async function subirImagenCloudinary(file) {
    const url = 'https://api.cloudinary.com/v1_1/dqntgsqp1/upload';

    const formData = new FormData();
    formData.append('file', file);
    formData.append('upload_preset', 'tu_upload_preset_unsigned');

    const response = await fetch(url, {
        method: 'POST',
        body: formData
    });

    if (!response.ok) throw new Error('Error al subir imagen');

    const data = await response.json();
    return data.secure_url; // o data.url
}


document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        const numero_error = document.getElementById('numeroError').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();
        const fileInput = document.getElementById('imagen');
        if (!fileInput.files.length) throw new Error('Debes seleccionar una imagen');

        const imagen_url = await subirImagenCloudinary(fileInput.files[0]);

        // Enviar datos al backend (ajusta URL si es necesario)
        const res = await fetch('/api/errores', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF()
            },
            body: JSON.stringify({ numero_error, descripcion, imagen_url })
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || 'Error al crear el registro');
        }

        new bootstrap.Modal(document.getElementById('createModal')).hide();
        await mostrarAlerta('¡Creado!', 'Error registrado correctamente', 'success');
        tablaErrores.ajax.reload(null, false);
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
});

async function eliminarError(id) {
    const confirmado = await Swal.fire({
        title: '¿Seguro que deseas eliminar este error?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar'
    });
    if (!confirmado.isConfirmed) return;

    const res = await fetch(`/api/errores/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': getCSRF() }
    });

    if (!res.ok) {
        const errData = await res.json();
        await mostrarAlerta('Error', errData.message || 'Error eliminando', 'error');
        return;
    }

    await mostrarAlerta('¡Eliminado!', 'Error eliminado correctamente', 'success');
    tablaErrores.ajax.reload(null, false);
}