// componentes/busqueda_inscripciones.js
const workerBusquedaInscrip = new Worker('db/worker.js');

const busqueda_inscripciones = {
    data() {
        return {
            buscar: '',
            listaInscripciones: []
        }
    },
    methods: {
        obtenerInscripciones() {
            // Le pedimos al worker que haga el JOIN y el filtrado directo en SQL
            workerBusquedaInscrip.postMessage({ 
                type: 'BUSCAR_INSCRIPCIONES_COMPLETAS', 
                data: { termino: this.buscar.trim() } 
            });

            workerBusquedaInscrip.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_BUSQUEDA_INSCRIPCIONES') {
                    this.listaInscripciones = e.data.data;
                } else if (e.data.type === 'ERROR') {
                    console.error("Error desde SQLite:", e.data.message);
                }
            };
        },
        eliminarInscripcion(id, e) {
            if(e) e.stopPropagation();
            
            alertify.confirm("Confirmar Eliminación", "¿Está seguro de eliminar esta inscripción?", 
                () => {
                    workerBusquedaInscrip.postMessage({ 
                        type: 'ELIMINAR_INSCRIPCION', 
                        data: { idInscripcion: id } 
                    });

                    workerBusquedaInscrip.onmessage = (e) => {
                        if (e.data.type === 'SUCCESS_ELIMINAR_INSCRIPCION') {
                            alertify.success('Inscripción eliminada');
                            this.obtenerInscripciones();
                        }
                    };
                },
                () => { // Cancelar
                }
            );
        }
    },
    mounted() {
        this.obtenerInscripciones();
    },
    template: `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span>INSCRIPCIONES REALIZADAS</span>
                        <div class="d-flex align-items-center">
                            <div class="input-group me-3" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                <input type="search" v-model="buscar" @keyup="obtenerInscripciones" class="form-control border-0 shadow-none" placeholder="Buscar alumno o materia...">
                            </div>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>FECHA</th>
                                        <th>ALUMNO</th>
                                        <th>MATERIA</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in listaInscripciones" :key="item.idInscripcion">
                                        <td>{{ item.fecha }}</td>
                                        <td>
                                            <div class="fw-bold">{{ item.nombreAlumno }}</div>
                                            <small class="text-muted">{{ item.codigoAlumno }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ item.nombreMateria }}</div>
                                            <small class="text-muted">{{ item.codigoMateria }}</small>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-danger btn-sm border-0" @click.stop="eliminarInscripcion(item.idInscripcion, $event)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
};