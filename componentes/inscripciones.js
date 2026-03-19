// componentes/inscripciones.js
const workerInscripciones = new Worker('db/worker.js');

const inscripciones = {
    props: ['forms'],
    components: {
        'v-select': window['vue-select']
    },
    data() {
        return {
            inscripcion: {
                idInscripcion: 0,
                idAlumno: '',
                idMateria: '',
                fecha: new Date().toISOString().split('T')[0],
                alumnoObj: null, 
                materiaObj: null 
            },
            listadoAlumnos: [],
            listadoMaterias: [],
            accion: 'nuevo',
            idInscripcion: 0
        }
    },
    watch: {
        "forms.inscripciones.mostrar"(nuevoValor) {
            if (nuevoValor) {
                this.cargarDatos(); 
            }
        }
    },
    methods: {
        buscarInscripcion() {
            this.forms.busqueda_inscripciones.mostrar = !this.forms.busqueda_inscripciones.mostrar;
            this.$emit('buscar');
        },
        cargarDatos() {
            // Pedimos los alumnos al worker
            workerInscripciones.postMessage({ type: 'OBTENER_LISTA_ALUMNOS' });
            // Pedimos las materias al worker
            workerInscripciones.postMessage({ type: 'OBTENER_LISTA_MATERIAS' });

            workerInscripciones.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_LISTA_ALUMNOS') {
                    // Formateamos para v-select
                    this.listadoAlumnos = e.data.data.map(a => ({
                        label: `${a.codigo} - ${a.nombre}`, id: a.idAlumno
                    }));
                }
                
                if (e.data.type === 'RESULTADO_LISTA_MATERIAS') {
                    // Formateamos para v-select
                    this.listadoMaterias = e.data.data.map(m => ({
                        label: `${m.codigo} - ${m.nombre}`, id: m.idMateria
                    }));
                }

                if (e.data.type === 'RESULTADO_VALIDAR_MATRICULA') {
                    // Aquí recibimos la respuesta de si el alumno está matriculado
                    if (e.data.estaMatriculado) {
                        this.ejecutarGuardado();
                    } else {
                        alertify.error("⛔ ERROR: Este alumno NO está matriculado.");
                    }
                }

                if (e.data.type === 'SUCCESS_GUARDAR_INSCRIPCION') {
                    alertify.success(this.accion === 'nuevo' ? "Materia inscrita correctamente." : "Inscripción actualizada.");
                    this.limpiarFormulario();
                    this.$emit('buscar');
                }

                if (e.data.type === 'ERROR') {
                    alertify.error("Error: " + e.data.message);
                }
            };
        },
        guardarInscripcion() {
            if (!this.inscripcion.alumnoObj || !this.inscripcion.alumnoObj.id) {
                alertify.warning("Seleccione un alumno.");
                return;
            }
            if (!this.inscripcion.materiaObj || !this.inscripcion.materiaObj.id) {
                alertify.warning("Seleccione una materia.");
                return;
            }

            let alumnoId = this.inscripcion.alumnoObj.id;

            // Antes de guardar, le pedimos al Worker que valide si el alumno está matriculado
            // La respuesta se manejará en el onmessage (RESULTADO_VALIDAR_MATRICULA)
            workerInscripciones.postMessage({ type: 'VALIDAR_MATRICULA', data: { idAlumno: alumnoId } });
        },
        ejecutarGuardado() {
            // Esta función se llama SOLO si el Worker confirmó que sí hay matrícula
            let datos = {
                idInscripcion: this.accion == 'modificar' ? this.idInscripcion : new Date().getTime(),
                idAlumno: this.inscripcion.alumnoObj.id,
                idMateria: this.inscripcion.materiaObj.id,
                fecha: this.inscripcion.fecha
            };

            workerInscripciones.postMessage({ type: 'GUARDAR_INSCRIPCION', data: datos });
        },
        limpiarFormulario() {
            this.accion = 'nuevo';
            this.idInscripcion = 0;
            this.inscripcion = {
                idInscripcion: 0, idAlumno: '', idMateria: '', fecha: new Date().toISOString().split('T')[0], alumnoObj: null, materiaObj: null
            };
        }
    },
    mounted() {
        this.cargarDatos();
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form @submit.prevent="guardarInscripcion" @reset.prevent="limpiarFormulario">
                    <div class="card card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center p-3">
                            <span><i class="bi bi-journal-check me-2"></i> {{ accion === 'nuevo' ? 'INSCRIPCIÓN DE MATERIAS' : 'EDITAR INSCRIPCIÓN' }}</span>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label-custom">ALUMNO:</label>
                                    <v-select 
                                        v-model="inscripcion.alumnoObj" 
                                        :options="listadoAlumnos"
                                        placeholder="Buscar alumno..."
                                    ></v-select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">MATERIA:</label>
                                    <v-select 
                                        v-model="inscripcion.materiaObj" 
                                        :options="listadoMaterias"
                                        placeholder="Buscar materia..."
                                    ></v-select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">FECHA:</label>
                                    <input required v-model="inscripcion.fecha" type="date" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-white border-0 pb-3">
                            <button type="submit" class="btn btn-primary btn-custom shadow-sm px-4">GUARDAR</button>
                            <button type="reset" class="btn btn-warning btn-custom shadow-sm px-4">NUEVO</button>
                            <button type="button" @click="buscarInscripcion" class="btn btn-success btn-custom shadow-sm px-4">BUSCAR</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `
};