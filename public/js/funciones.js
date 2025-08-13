let tablaUsuarios, tablaErrores;

function getCSRF() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

// Función para inicializar DataTables con botones preconfigurados
function inicializarDataTableConBotones(selector, opcionesExtras = {}) {
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().clear().destroy();
    }

    const opcionesBase = {
        dom: 'Bfrtip', // <-- Aquí definimos la ubicación de los botones
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i class="bi bi-clipboard me-1"></i>Copiar',
                className: 'btn btn-secondary btn-sm me-1'
            },
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm me-1'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm me-1'
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i>Imprimir',
                className: 'btn btn-info btn-sm'
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true
    };

    const opcionesFinales = $.extend(true, {}, opcionesBase, opcionesExtras);
    return $(selector).DataTable(opcionesFinales);
}

function inicializarDataTable() {
    tablaUsuarios = inicializarDataTableConBotones('#tabla-usuarios', {
        ajax: {
            url: '/api/usuarios',
            dataSrc: '',
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nombre' },
            { data: 'email' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
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
        paging: true,
        pageLength: 10,
        lengthChange: false,
        searching: true,
    });
}

function inicializarDataTableErrores() {
    tablaErrores = inicializarDataTableConBotones('#tabla-errores', {
        ajax: {
            url: '/api/errores',
            dataSrc: ''
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'numero_error' },
            { data: 'descripcion' },
            { data: 'imagen_url', render: (data) => data ? `<img src="${data}" style="max-height:50px;">` : 'No hay' },
            { data: 'created_at' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: (data) => `
                    <button class="btn btn-sm btn-primary me-1" onclick="abrirModalError(${data})">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarError(${data})">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                `
            }
        ],
        paging: true,
        pageLength: 10,
        lengthChange: false,
        searching: true,
    });
}

// SweetAlert helpers
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

// API request helper
async function realizarPeticion(url, metodo = 'GET', data = null, isFormData = false) {
    const opciones = {
        method: metodo,
        headers: {
            'X-CSRF-TOKEN': getCSRF()
        }
    };

    if (data) {
        if (isFormData) {
            opciones.headers['Accept'] = 'application/json';
            opciones.body = data; // FormData
        } else {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(data);
        }
    }

    const res = await fetch(url, opciones);
    if (!res.ok) {
        let mensajeError = `Error: ${res.status} ${res.statusText}`;
        try {
            const json = await res.json();
            mensajeError = json.message || (json.errors ? Object.values(json.errors).flat().join(', ') : mensajeError);
        } catch {
            try { mensajeError = await res.text(); } catch {}
        }
        throw new Error(mensajeError);
    }
    return res;
}

// Table reload helper
function recargarTabla(tabla) {
    if (tabla) {
        tabla.ajax.reload(null, false);
    }
}

// User management
function abrirModalCrear() {
    document.getElementById("createNombre").value = '';
    document.getElementById("createEmail").value = '';
    document.getElementById("createPassword").value = '';
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
        recargarTabla(tablaUsuarios);
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
        recargarTabla(tablaUsuarios);
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
        recargarTabla(tablaUsuarios);
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
}

// Error management
function abrirModalCrearError() {
    document.getElementById('numeroError').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('imagen').value = '';
    new bootstrap.Modal(document.getElementById('createModal')).show();
}

async function crearError(formData) {
    try {
        await realizarPeticion('/api/errores', 'POST', formData, true);
        bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
        await mostrarAlerta('¡Creado!', 'Error registrado correctamente', 'success');
        recargarTabla(tablaErrores);
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
}

async function eliminarError(id) {
    const confirmado = await confirmarAccion('¿Seguro que deseas eliminar este error?');
    if (!confirmado) return;

    try {
        await realizarPeticion(`/errores/${id}`, 'DELETE');
        await mostrarAlerta('¡Eliminado!', 'Error eliminado correctamente', 'success');
        recargarTabla(tablaErrores);
    } catch (error) {
        await mostrarAlerta('Error', error.message, 'error');
    }
}

function abrirModalError(id) {
    fetch(`/errores/${id}`)
        .then(res => {
            if (!res.ok) throw new Error('No se pudo obtener el error');
            return res.json();
        })
        .then(data => {
            document.getElementById('editId').value = data.id;
            document.getElementById('editNumeroError').value = data.numero_error;
            document.getElementById('editDescripcion').value = data.descripcion || '';

            const preview = document.getElementById('editPreview');
            preview.innerHTML = data.imagen_url 
                ? `<img src="${data.imagen_url}" style="max-height:80px;">`
                : 'No hay imagen';

            new bootstrap.Modal(document.getElementById('editModal')).show();
        })
        .catch(err => {
            mostrarAlerta('Error', err.message, 'error');
        });
}

document.getElementById('editFormerror').addEventListener('submit', async (e) => {
    e.preventDefault();

    const confirmar = await confirmarAccion("¿Deseas guardar los cambios?");
    if (!confirmar) return; // Si el usuario cancela, no hace nada

    const id = document.getElementById('editId').value;
    const formData = new FormData(e.target);

    const res = await fetch(`/errores/${id}`, {
        method: 'POST', // Laravel requiere POST con _method=PUT
        body: formData
    });

    if (!res.ok) {
        let msg = 'Error al actualizar';
        try {
            const err = await res.json();
            msg = err.message || msg;
        } catch {}
        return mostrarAlerta('Error', msg, 'error');
    }

    await mostrarAlerta('¡Actualizado!', 'Error actualizado correctamente', 'success');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    tablaErrores.ajax.reload(null, false);
});

// DOM ready initialization
document.addEventListener('DOMContentLoaded', () => {
    // Initialize tables if present
    if (document.getElementById('tabla-usuarios')) {
        inicializarDataTable();
    }
    if (document.getElementById('tabla-errores')) {
        inicializarDataTableErrores();
    }

    // User form listeners
    const createUserForm = document.getElementById("createForm");
    if (createUserForm && document.getElementById("createNombre")) {
        createUserForm.addEventListener("submit", function (e) {
            e.preventDefault();
            crearUsuario();
        });
    }

    const editForm = document.getElementById("editForm");
    if (editForm) {
        editForm.addEventListener("submit", function (e) {
            e.preventDefault();
            actualizarUsuario();
        });
    }

    // Error form listener
    const createErrorForm = document.getElementById('createForm');
    if (createErrorForm && document.getElementById('numeroError')) {
        createErrorForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(createErrorForm);
            await crearError(formData);
        });
    }
});