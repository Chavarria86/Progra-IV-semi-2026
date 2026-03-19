// componentes/busqueda_docentes.js
const workerBusquedaDocente = new Worker('db/worker.js');

const busqueda_docentes = {
    data() {
        return { buscar: '', docentes: [] }
    },
    methods: {
        modificarDocente(docente) {
            this.$emit('modificar', docente);
        },
        obtenerDocentes() {
            workerBusquedaDocente.postMessage({ type: 'BUSCAR_DOCENTES', data: { termino: this.buscar.trim() } });
            workerBusquedaDocente.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_BUSQUEDA_DOCENTES') {
                    this.docentes = e.data.data;
                }
            };
        },
        eliminarDocente(idDocente, e) {
            if(e) e.stopPropagation();
            alertify.confirm("Eliminar Docente", "¿Desea eliminar este registro?", 
                () => {
                    workerBusquedaDocente.postMessage({ type: 'ELIMINAR_DOCENTE', data: { idDocente: idDocente } });
                    workerBusquedaDocente.onmessage = (e) => {
                        if (e.data.type === 'SUCCESS_ELIMINAR_DOCENTE') {
                            alertify.success('Docente eliminado');
                            this.obtenerDocentes();
                        }
                    };
                }, null
            );
        }
    },
    mounted() { this.obtenerDocentes(); },
    template: `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span>LISTADO DE DOCENTES</span>
                        <div class="d-flex align-items-center">
                            <div class="input-group me-3" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                <input type="search" v-model="buscar" @keyup="obtenerDocentes" class="form-control border-0 shadow-none" placeholder="Buscar...">
                            </div>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>CÓDIGO</th>
                                    <th>NOMBRE</th>
                                    <th>TELÉFONO</th>
                                    <th>ESCALAFÓN</th>
                                    <th class="text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in docentes" :key="d.idDocente" @click="modificarDocente(d)" style="cursor: pointer">
                                    <td class="fw-bold text-primary">{{ d.codigo }}</td>
                                    <td>{{ d.nombre }}</td>
                                    <td>{{ d.telefono }}</td>
                                    <td><span class="badge bg-info text-dark">{{ d.escalafon }}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-danger btn-sm border-0" @click.stop="eliminarDocente(d.idDocente, $event)">
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
    `
};