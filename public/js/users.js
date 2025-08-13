document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("errorModal");
    if (!modal) return; // Si no existe el modal, no hacer nada

    modal.addEventListener("show.bs.modal", event => {
        const card = event.relatedTarget;

        document.getElementById("modalImagen").src = card.getAttribute("data-imagen") || "";
        document.getElementById("modalNumero").innerText = "Error #" + card.getAttribute("data-numero");

        // Parsear links y permitir clic
        let descripcion = card.getAttribute("data-descripcion");
        descripcion = descripcion.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" style="color:#00ff7f;">$1</a>');
        document.getElementById("modalDescripcion").innerHTML = descripcion;

        document.getElementById("modalFecha").innerText = "Creado: " + card.getAttribute("data-fecha");
    });
});
