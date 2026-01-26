ocument.addEventListener("DOMContentLoaded", () => {
    frmAlumnos.addEventListener("submit", (e) => {
        e.preventDefault();
        guardarAlumno();

       
    });
});
function guardarAlumno() {
    let datos = {
            codigo: txtCodigoAlumno.value,
            nombre: txtnombreAlumno.value,
            direccion: txtDireccionAlumno.value,
            email: txtEmailAlumno.value,
            telefono: txtTelefonoAlumno.value
    };
    localStorage.setItem("alumno", JSON.stringify(datos));
};