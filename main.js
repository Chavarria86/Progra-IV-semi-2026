document.addEventListener("DOMContentLoaded", () => {
    const frm = document.querySelector("#frmAlumnos");
    const txtBuscar = document.querySelector("#txtBuscarAlumno");
    const btnBuscar = document.querySelector("#btnBuscar");

    frm.addEventListener("submit", (e) => {
        e.preventDefault();
        guardarAlumno();
    });

    txtBuscar.addEventListener("input", () => {
        mostrarAlumnos(txtBuscar.value);
    });

    btnBuscar.addEventListener("click", () => {
        mostrarAlumnos(txtBuscar.value);
    });

    mostrarAlumnos();
});

function guardarAlumno() {
    const alumno = {
        codigo: txtCodigoAlumno.value,
        nombre: txtnombreAlumno.value,
        direccion: txtDireccionAlumno.value,
        municipio: txtMunicipioAlumno.value,
        departamento: txtDeptoAlumno.value,
        fechaNac: txtFechaAlumno.value,
        sexo: txtSexoAlumno.value,
        email: txtEmailAlumno.value,
        telefono: txtTelefonoAlumno.value
    };

    localStorage.setItem(alumno.codigo, JSON.stringify(alumno));
    alert("Registro guardado correctamente.");
    frmAlumnos.reset();
    mostrarAlumnos();
}

function mostrarAlumnos(filtro = "") {
    const tbody = document.querySelector("#tblAlumnos tbody");
    let filas = "";
    filtro = filtro.toLowerCase();

    for (let i = 0; i < localStorage.length; i++) {
        const clave = localStorage.key(i);
        const data = JSON.parse(localStorage.getItem(clave));

        if (data && data.codigo) {

            const textoAlumno = `
                ${data.codigo} ${data.nombre} ${data.direccion}
                ${data.municipio} ${data.departamento}
                ${data.fechaNac} ${data.sexo}
                ${data.email} ${data.telefono}
            `.toLowerCase();

            if (textoAlumno.includes(filtro)) {
                filas += `
                    <tr onclick='modificarAlumno(${JSON.stringify(data)})'>
                        <td class="fw-bold codigo-alumno">${data.codigo}</td>
                        <td>${data.nombre}</td>
                        <td title="${data.direccion}">${data.direccion}</td>
                        <td><small>${data.municipio}, ${data.departamento}</small></td>
                        <td>${data.sexo}</td>
                        <td>
                            <div class="small">${data.email}</div>
                            <div class="fw-bold small">${data.telefono}</div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-danger-custom btn-sm" onclick="event.stopPropagation(); eliminarAlumno('${data.codigo}')">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            }
        }
    }
    tbody.innerHTML = filas;
}

function modificarAlumno(alumno) {
    txtCodigoAlumno.value = alumno.codigo;
    txtnombreAlumno.value = alumno.nombre;
    txtDireccionAlumno.value = alumno.direccion;
    txtMunicipioAlumno.value = alumno.municipio;
    txtDeptoAlumno.value = alumno.departamento;
    txtFechaAlumno.value = alumno.fechaNac;
    txtSexoAlumno.value = alumno.sexo;
    txtEmailAlumno.value = alumno.email;
    txtTelefonoAlumno.value = alumno.telefono;
}

function eliminarAlumno(id) {
    if (confirm("¿Seguro que desea borrar este alumno?")) {
        localStorage.removeItem(id);
        mostrarAlumnos();
    }
}
