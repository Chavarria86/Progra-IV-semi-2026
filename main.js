document.addEventListener("DOMContentLoaded", () => {
    const frm = document.querySelector("#frmAlumnos");
    frm.addEventListener("submit", (e) => {
        e.preventDefault();
        guardarAlumno();
    });
    mostrarAlumnos();
});

function guardarAlumno() {
    const alumno = {
        codigo: document.querySelector("#txtCodigoAlumno").value,
        nombre: document.querySelector("#txtnombreAlumno").value,
        direccion: document.querySelector("#txtDireccionAlumno").value,
        municipio: document.querySelector("#txtMunicipioAlumno").value,
        departamento: document.querySelector("#txtDeptoAlumno").value,
        fechaNac: document.querySelector("#txtFechaAlumno").value,
        sexo: document.querySelector("#txtSexoAlumno").value,
        email: document.querySelector("#txtEmailAlumno").value,
        telefono: document.querySelector("#txtTelefonoAlumno").value
    };

    localStorage.setItem(alumno.codigo, JSON.stringify(alumno));
    alert("Registro actualizado correctamente.");
    document.querySelector("#frmAlumnos").reset();
    mostrarAlumnos();
}

function mostrarAlumnos() {
    const $tbody = document.querySelector("#tblAlumnos tbody");
    let filas = "";

    for (let i = 0; i < localStorage.length; i++) {
        const clave = localStorage.key(i);
        const data = JSON.parse(localStorage.getItem(clave));

        if (data && data.codigo) {
            filas += `
                <tr onclick='modificarAlumno(${JSON.stringify(data)})'>
                    <td class="fw-bold text-primary">${data.codigo}</td>
                    <td>${data.nombre}</td>
                    <td class="col-direccion" title="${data.direccion}">${data.direccion}</td>
                    <td><small>${data.municipio}, ${data.departamento}</small></td>
                    <td>${data.sexo}</td>
                    <td>
                        <div class="small">${data.email}</div>
                        <div class="fw-bold small">${data.telefono}</div>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-outline-danger btn-sm" onclick="event.stopPropagation(); eliminarAlumno('${data.codigo}')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            `;
        }
    }
    $tbody.innerHTML = filas;
}

function modificarAlumno(alumno) {
    document.querySelector("#txtCodigoAlumno").value = alumno.codigo;
    document.querySelector("#txtnombreAlumno").value = alumno.nombre;
    document.querySelector("#txtDireccionAlumno").value = alumno.direccion;
    document.querySelector("#txtMunicipioAlumno").value = alumno.municipio;
    document.querySelector("#txtDeptoAlumno").value = alumno.departamento;
    document.querySelector("#txtFechaAlumno").value = alumno.fechaNac;
    document.querySelector("#txtSexoAlumno").value = alumno.sexo;
    document.querySelector("#txtEmailAlumno").value = alumno.email;
    document.querySelector("#txtTelefonoAlumno").value = alumno.telefono;
    document.querySelector("#txtnombreAlumno").focus();
}

function eliminarAlumno(id) {
    if (confirm("¿Seguro que desea borrar este alumno?")) {
        localStorage.removeItem(id);
        mostrarAlumnos();
    }
}