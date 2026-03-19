// componentes/busqueda_materias.js
const workerBusquedaMateria = new Worker('db/worker.js');

const busqueda_materias = {
    data() {
        return { buscar: '', materias: [] }
    },
    methods: {
        modificarMateria(materia) {
            this.$emit('modificar', materia);
        },
        obtenerMaterias() {
            workerBusquedaMateria.postMessage({ type: 'BUSCAR_MATERIAS', data: { termino: this.buscar.trim() } });
            
            workerBusquedaMateria.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_BUSQUEDA_MATERIAS') {
                    this.materias = e.data.data;
                }
            };
        },
        eliminarMateria(idMateria, e) {
            if(e) e.stopPropagation(); 
            
            alertify.confirm("Confirmar Eliminación", "¿Está seguro de eliminar esta materia?", 
                () => {
                    workerBusquedaMateria.postMessage({ type: 'ELIMINAR_MATERIA', data: { idMateria: idMateria } });
                    workerBusquedaMateria.onmessage = (e) => {
                        if (e.data.type === 'SUCCESS_ELIMINAR_MATERIA') {
                            alertify.success('Materia eliminada');
                            this.obtenerMaterias(); 
                        }
                    };
                },
                () => { }
            );
        }
    },
    mounted() {
        this.obtenerMaterias();
    },
    template: `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span>LISTADO DE MATERIAS</span>
                        <div class="d-flex align-items-center">
                            <div class="input-group me-3" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                <input type="search" v-model="buscar" @keyup="obtenerMaterias" class="form-control border-0 shadow-none" placeholder="Buscar...">
                            </div>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>CÓDIGO</th>
                                        <th>NOMBRE</th>
                                        <th>UV</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="materia in materias" :key="materia.idMateria" @click="modificarMateria(materia)" style="cursor: pointer">
                                        <td class="fw-bold text-primary">{{ materia.codigo }}</td>
                                        <td>{{ materia.nombre }}</td>
                                        <td>{{ materia.uv }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-warning btn-sm border-0 me-2" @click.stop="modificarMateria(materia)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm border-0" @click.stop="eliminarMateria(materia.idMateria, $event)">
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