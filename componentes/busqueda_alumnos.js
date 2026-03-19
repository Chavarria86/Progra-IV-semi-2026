// componentes/busqueda_alumnos.js
// Creamos la instancia del worker apuntando al mismo archivo
const workerBusqueda = new Worker('/db/worker.js');

const busqueda_alumnos = {
    data() {
        return {
            buscar: '',
            alumnos: []
        }
    },
    methods: {
        modificarAlumno(alumno) {
            this.$emit('modificar', alumno);
        },
        obtenerAlumnos() {
            // Enviamos el término de búsqueda al Worker
            workerBusqueda.postMessage({ 
                type: 'BUSCAR_ALUMNOS', 
                data: { termino: this.buscar.trim() } 
            });

            // Escuchamos la respuesta
            workerBusqueda.onmessage = (e) => {
                if (e.data.type === 'RESULTADO_BUSQUEDA') {
                    this.alumnos = e.data.data;
                } else if (e.data.type === 'ERROR') {
                    console.error("Error cargando alumnos desde SQLite:", e.data.message);
                }
            };
        },
        eliminarAlumno(idAlumno, e) {
            if(e) e.stopPropagation(); 
            
            // Usamos alertify para mantener la consistencia con el diseño chill
            alertify.confirm("Confirmar Eliminación", "¿Está seguro de eliminar este registro permanentemente?", 
                () => { // Si el usuario dice que sí
                    workerBusqueda.postMessage({ 
                        type: 'ELIMINAR_ALUMNO', 
                        data: { idAlumno: idAlumno } 
                    });

                    workerBusqueda.onmessage = (e) => {
                        if (e.data.type === 'SUCCESS_ELIMINAR') {
                            alertify.success('Registro eliminado');
                            this.obtenerAlumnos(); // Refrescamos la tabla
                        }
                    };
                },
                () => { // Si el usuario cancela
                    // No hacemos nada
                }
            );
        }
    },
    mounted() {
        this.obtenerAlumnos();
    },
    template: `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span>LISTADO DE ALUMNOS</span>
                        
                        <div class="d-flex align-items-center">
                            <div class="input-group me-3" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                <input type="search" v-model="buscar" @keyup="obtenerAlumnos" class="form-control border-0 shadow-none" placeholder="Buscar...">
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
                                        <th>DIRECCIÓN</th>
                                        <th>EMAIL</th>
                                        <th>TELÉFONO</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="alumno in alumnos" :key="alumno.idAlumno" @click="modificarAlumno(alumno)" style="cursor: pointer">
                                        <td class="fw-bold text-primary">{{ alumno.codigo }}</td>
                                        <td>{{ alumno.nombre }}</td>
                                        <td>{{ alumno.direccion }}</td>
                                        <td>{{ alumno.email }}</td>
                                        <td>{{ alumno.telefono }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-warning btn-sm border-0 me-2" @click.stop="modificarAlumno(alumno)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm border-0" @click.stop="eliminarAlumno(alumno.idAlumno, $event)">
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