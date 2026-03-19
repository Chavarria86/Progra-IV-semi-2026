const worker = new Worker('/db/worker.js');

const alumnos = {
    props: ['forms'],
    data() {
        return {
            alumno: {
                idAlumno: 0, codigo: '', nombre: '', direccion: '', municipio: '', 
                departamento: '', email: '', telefono: '', fechaNac: '', sexo: 'Masculino'
            },
            accion: 'nuevo',
            idAlumno: 0 
        }
    },
    methods: {
        buscarAlumno() {
            this.forms.busqueda_alumnos.mostrar = !this.forms.busqueda_alumnos.mostrar;
            this.$emit('buscar'); 
        },
        modificarAlumno(alumno) {
            this.accion = 'modificar';
            this.idAlumno = alumno.idAlumno;
            this.alumno = { ...alumno }; 
        },
        guardarAlumno() {
            console.log("1. Botón presionado. Preparando datos...");
            let datos = { ...this.alumno };
            datos.idAlumno = this.accion == 'modificar' ? this.idAlumno : new Date().getTime();

            console.log("2. Enviando datos a SQLite...", datos);
            worker.postMessage({ type: 'GUARDAR_ALUMNO', data: datos });

            worker.onmessage = (e) => {
                console.log("3. El Worker respondió:", e.data);
                
                if (e.data.type === 'SUCCESS_ALUMNO') {
                    // Plan B: Si Alertify falló por la ruta, usamos el alert normal para no congelar
                    if (typeof alertify !== 'undefined') {
                        alertify.success(this.accion === 'nuevo' ? "Alumno registrado." : "Alumno actualizado.");
                    } else {
                        alert("¡Éxito! Alumno guardado (Ojo: Alertify no está cargando bien).");
                    }
                    
                    this.limpiarFormulario();
                    this.$emit('buscar'); 
                } else if (e.data.type === 'ERROR') {
                    console.error("Error devuelto por SQLite:", e.data.message);
                    if (e.data.message.includes("UNIQUE constraint failed")) {
                        alert("Error: Ese CÓDIGO ya existe en la base de datos.");
                    } else {
                        alert("Error al guardar: " + e.data.message);
                    }
                }
            };
        },
        limpiarFormulario() {
            this.accion = 'nuevo';
            this.idAlumno = 0;
            this.alumno = {
                idAlumno: 0, codigo: '', nombre: '', direccion: '', municipio: '', 
                departamento: '', email: '', telefono: '', fechaNac: '', sexo: 'Masculino'
            };
        }
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form @submit.prevent="guardarAlumno" @reset.prevent="limpiarFormulario">
                    <div class="card card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center p-3">
                            <span>REGISTRO DE ALUMNO</span>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label-custom">CÓDIGO:</label><input required v-model="alumno.codigo" type="text" class="form-control" :disabled="accion === 'modificar'"></div>
                                <div class="col-md-6"><label class="form-label-custom">NOMBRE:</label><input required v-model="alumno.nombre" type="text" class="form-control"></div>
                                <div class="col-12"><label class="form-label-custom">DIRECCIÓN:</label><input required v-model="alumno.direccion" type="text" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label-custom">MUNICIPIO:</label><input required v-model="alumno.municipio" type="text" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label-custom">DEPTO:</label><input required v-model="alumno.departamento" type="text" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label-custom">EMAIL:</label><input required v-model="alumno.email" type="email" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label-custom">TELÉFONO:</label><input required v-model="alumno.telefono" type="text" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label-custom">FECHA NAC:</label><input required v-model="alumno.fechaNac" type="date" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label-custom">SEXO:</label>
                                    <select v-model="alumno.sexo" class="form-select"><option>Masculino</option><option>Femenino</option></select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-white border-0 pb-3">
                            <button type="submit" class="btn btn-primary btn-custom">GUARDAR</button>
                            <button type="reset" class="btn btn-warning btn-custom">NUEVO</button>
                            <button type="button" @click="buscarAlumno" class="btn btn-success btn-custom">BUSCAR</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `
};