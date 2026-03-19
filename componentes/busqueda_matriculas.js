// componentes/busqueda_matriculas.js
const workerBusquedaMatriculas = new Worker('db/worker.js');

const busqueda_matriculas = {
    data() {
        return {
            buscar: '',
            listaMatriculas: [] 
        }
    },
    methods: {
        modificarMatricula(matricula) {
            this.$emit('modificar', matricula);
        },
        obtenerMatriculas() {
            // Mandamos a buscar con JOIN directamente en SQL
            workerBusquedaMatriculas.postMessage({ 
                type: 'BUSCAR_MATRICULAS_COMPLETAS', 
                data: { termino: this.buscar.trim() } 
            });

            workerBusquedaMatriculas.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_BUSQUEDA_MATRICULAS') {
                    this.listaMatriculas = e.data.data;
                } else if (e.data.type === 'ERROR') {
                    console.error("Error desde SQLite:", e.data.message);
                }
            };
        },
        eliminarMatricula(id, e) {
            if(e) e.stopPropagation();
            
            alertify.confirm("Confirmar Eliminación", "¿Está seguro de eliminar esta matrícula?", 
                () => {
                    workerBusquedaMatriculas.postMessage({ 
                        type: 'ELIMINAR_MATRICULA', 
                        data: { idMatricula: id } 
                    });

                    workerBusquedaMatriculas.onmessage = (e) => {
                        if (e.data.type === 'SUCCESS_ELIMINAR_MATRICULA') {
                            alertify.success('Matrícula eliminada');
                            this.obtenerMatriculas();
                        }
                    };
                },
                () => { // Cancelar
                }
            );
        }
    },
    mounted() {
        this.obtenerMatriculas();
    },
    template: `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span>HISTORIAL DE MATRÍCULAS</span>
                        <div class="d-flex align-items-center">
                            <div class="input-group me-3" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                <input type="search" v-model="buscar" @keyup="obtenerMatriculas" class="form-control border-0 shadow-none" placeholder="Buscar alumno o ciclo...">
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
                                        <th>CÓDIGO</th>
                                        <th>ESTUDIANTE</th>
                                        <th>CICLO</th>
                                        <th class="text-center">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in listaMatriculas" :key="item.idMatricula" @click="modificarMatricula(item)" style="cursor: pointer">
                                        <td>{{ item.fecha }}</td>
                                        <td class="fw-bold text-primary">{{ item.codigoAlumno }}</td>
                                        <td>{{ item.nombreAlumno }}</td>
                                        <td><span class="badge bg-success">{{ item.ciclo }}</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-warning btn-sm border-0 me-2" @click.stop="modificarMatricula(item)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm border-0" @click.stop="eliminarMatricula(item.idMatricula, $event)">
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