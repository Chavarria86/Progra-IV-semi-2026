// componentes/matriculas.js
const workerMatricula = new Worker('db/worker.js');

const matriculas = {
    props: ['forms'],
    components: {
        'v-select': window['vue-select']
    },
    data() {
        return {
            matricula: {
                idMatricula: 0,
                idAlumno: '',
                fecha: new Date().toISOString().split('T')[0], 
                ciclo: '1-2026',
                alumnoObj: null
            },
            listadoAlumnos: [],
            accion: 'nuevo',
            idMatricula: 0
        }
    },
    watch: {
        "forms.matriculas.mostrar"(nuevoValor) {
            if (nuevoValor) {
                this.cargarAlumnos();
            }
        }
    },
    methods: {
        buscarMatricula() {
            this.forms.busqueda_matriculas.mostrar = !this.forms.busqueda_matriculas.mostrar;
            this.$emit('buscar');
        },
        cargarAlumnos() {
            // Le pedimos al worker la lista de alumnos
            workerMatricula.postMessage({ type: 'OBTENER_LISTA_ALUMNOS' });
            
            // Centralizamos las respuestas del worker aquí
            workerMatricula.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_LISTA_ALUMNOS') {
                    this.listadoAlumnos = e.data.data.map(a => ({
                        label: `${a.codigo} - ${a.nombre}`, id: a.idAlumno
                    }));
                }
                
                if (e.data.type === 'SUCCESS_GUARDAR_MATRICULA') {
                    alertify.success(this.accion === 'nuevo' ? "Matrícula registrada." : "Matrícula actualizada.");
                    this.limpiarFormulario();
                    this.$emit('buscar');
                }
                
                if (e.data.type === 'ERROR') {
                    alertify.error("Error al guardar: " + e.data.message);
                }
            };
        },
        modificarMatricula(datos) {
            this.accion = 'modificar';
            this.idMatricula = datos.idMatricula;
            this.matricula = { ...datos };
            
            // Como el buscador ahora usará SQL (JOIN), ya nos pasará el código y nombre
            if(datos.idAlumno && datos.codigoAlumno && datos.nombreAlumno){
                this.matricula.alumnoObj = {
                    label: `${datos.codigoAlumno} - ${datos.nombreAlumno}`,
                    id: datos.idAlumno
                };
            }
        },
        guardarMatricula() {
            if (!this.matricula.alumnoObj || !this.matricula.alumnoObj.id) {
                alertify.warning("Debe seleccionar un alumno.");
                return;
            }

            let datos = {
                idMatricula: this.accion == 'modificar' ? this.idMatricula : new Date().getTime(),
                idAlumno: this.matricula.alumnoObj.id,
                fecha: this.matricula.fecha,
                ciclo: this.matricula.ciclo
            };

            workerMatricula.postMessage({ type: 'GUARDAR_MATRICULA', data: datos });
        },
        limpiarFormulario() {
            this.accion = 'nuevo';
            this.idMatricula = 0;
            this.matricula = {
                idMatricula: 0, idAlumno: '', fecha: new Date().toISOString().split('T')[0], ciclo: '1-2026', alumnoObj: null
            };
        }
    },
    mounted() {
        this.cargarAlumnos();
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form @submit.prevent="guardarMatricula" @reset.prevent="limpiarFormulario">
                    <div class="card card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center p-3">
                            <span><i class="bi bi-credit-card-2-front me-2"></i> {{ accion === 'nuevo' ? 'REGISTRO DE MATRÍCULA' : 'EDITAR MATRÍCULA' }}</span>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label-custom">ALUMNO (BUSCADOR):</label>
                                    <v-select 
                                        v-model="matricula.alumnoObj" 
                                        :options="listadoAlumnos"
                                        placeholder="Buscar por nombre o código..."
                                    ></v-select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">CICLO:</label>
                                    <select required v-model="matricula.ciclo" class="form-select">
                                        <option value="1-2026">1-2026</option>
                                        <option value="2-2026">2-2026</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">FECHA:</label>
                                    <input required v-model="matricula.fecha" type="date" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-white border-0 pb-3">
                            <button type="submit" class="btn btn-primary btn-custom shadow-sm px-4">GUARDAR</button>
                            <button type="reset" class="btn btn-warning btn-custom shadow-sm px-4">NUEVO</button>
                            <button type="button" @click="buscarMatricula" class="btn btn-success btn-custom shadow-sm px-4">BUSCAR</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `
};