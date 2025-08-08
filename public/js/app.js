// Cambio de colores 

document.querySelectorAll('.theme-select').forEach(el => {
    el.addEventListener('click', e => {
        e.preventDefault();
        const theme = e.target.getAttribute('data-theme');
        // Remover todas las clases de tema
        document.body.classList.remove('theme-light', 'theme-dark', 'theme-blue');
        // Agregar la nueva clase
        document.body.classList.add('theme-' + theme);
        // Guardar en localStorage
        localStorage.setItem('theme', theme);
    });
});

// Al cargar la página, aplicar tema guardado o por defecto 'light'
window.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.classList.add('theme-' + savedTheme);
});

// visualizar contenido lateral 

document.getElementById('sidebarToggle').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('d-none');
});

