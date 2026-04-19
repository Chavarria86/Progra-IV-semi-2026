const inscripciones = {
    props:['forms'],
    data(){
        return{
            inscripcion:{
                idAlumno:"",
                idMateria:"",
                fecha:""
            },
            accion:'nuevo',
            idInscripcion:0,
            data_inscripciones:[],
            busqueda: ''
        }
    },
    mounted() {
        this.obtenerInscripciones();
    },
    methods:{
        buscarInscripcion(){
            this.obtenerInscripciones();
        },
        modificarInscripcion(inscripcion){
            this.accion = 'modificar';
            this.idInscripcion = inscripcion.idInscripcion;
            this.inscripcion.idAlumno = inscripcion.idAlumno;
            this.inscripcion.idMateria = inscripcion.idMateria;
            this.inscripcion.fecha = inscripcion.fecha;
        },
        async eliminarInscripcion(inscripcion) {
            if(!confirm(`¿Estás seguro de eliminar esta inscripción?`)) return;
            
            try {
                let response = await fetch(`/api/inscripciones/${inscripcion.idInscripcion}`, {
                    method: 'DELETE'
                });
                
                let data = await response.json();
                if(response.ok) {
                    if(typeof alertify !== 'undefined') alertify.success('Inscripción eliminada');
                    this.obtenerInscripciones();
                } else {
                    if(typeof alertify !== 'undefined') alertify.error(`Error: ${data.message}`);
                }
            } catch(e) {
                if(typeof alertify !== 'undefined') alertify.error('Error al conectar con la API');
            }
        },
        async guardarInscripcion() {
            let datos = {
                idAlumno: this.inscripcion.idAlumno,
                idMateria: this.inscripcion.idMateria,
                fecha: this.inscripcion.fecha
            };

            let method = this.accion === 'modificar' ? 'PUT' : 'POST';
            let url = this.accion === 'modificar' ? `/api/inscripciones/${this.idInscripcion}` : '/api/inscripciones';

            try {
                let response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                let data = await response.json();
                
                if(!response.ok) {
                    if(typeof alertify !== 'undefined') alertify.error(`Error del servidor: ${data.message || 'Desconocido'}`);
                } else {
                    if(typeof alertify !== 'undefined') alertify.success(`Inscripción procesada correctamente`);
                    this.limpiarFormulario();
                    this.obtenerInscripciones();
                }
            } catch(e) {
                if(typeof alertify !== 'undefined') alertify.error('Error al conectar con la API de Laravel');
            }
        },
        async obtenerInscripciones() {
            let url = '/api/inscripciones';
            if (this.busqueda.trim() !== '') {
                url += `?buscar=${encodeURIComponent(this.busqueda)}`;
            }
            try {
                let response = await fetch(url);
                if(response.ok) {
                    this.data_inscripciones = await response.json();
                }
            } catch(e) { }
        },
        limpiarFormulario(){
            this.accion = 'nuevo';
            this.idInscripcion = 0;
            this.inscripcion.idAlumno = '';
            this.inscripcion.idMateria = '';
            this.inscripcion.fecha = '';
        },
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">📋 REGISTRO DE INSCRIPCIONES</h4>
                        <button type="reset" form="frmInscripciones" class="btn btn-sm btn-outline-light">Nuevo / Limpiar</button>
                    </div>
                    
                    <div class="card-body">
                        <form id="frmInscripciones" @submit.prevent="guardarInscripcion" @reset.prevent="limpiarFormulario">
                            <div class="row g-3">
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ID ALUMNO (UUID):</label>
                                    <input placeholder="Añade el UUID del alumno" required v-model="inscripcion.idAlumno" type="text" class="form-control">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ID MATERIA (UUID):</label>
                                    <input placeholder="Añade el UUID de la materia" required v-model="inscripcion.idMateria" type="text" class="form-control">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">FECHA:</label>
                                    <input required v-model="inscripcion.fecha" type="date" class="form-control">
                                </div>
                                
                            </div>
                            
                            <hr class="my-4">
                            
                            <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                                {{ accion === 'nuevo' ? 'CREAR INSCRIPCIÓN' : 'ACTUALIZAR INSCRIPCIÓN' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">LISTA VIVA DE INSCRIPCIONES</h4>
                    </div>
                    
                    <div class="card-body">
                        <div class="input-group mb-4">
                            <span class="input-group-text bg-light">🔍</span>
                            <input placeholder="Buscar inscripciones..." v-model="busqueda" @keyup="buscarInscripcion" type="text" class="form-control">
                            <button type="button" @click="buscarInscripcion" class="btn btn-secondary px-4">Buscar</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>ID ALUMNO</th>
                                        <th>ID MATERIA</th>
                                        <th>FECHA</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ins in data_inscripciones" :key="ins.idInscripcion" :class="idInscripcion === ins.idInscripcion ? 'table-primary' : ''">
                                        <td><strong>{{ ins.idInscripcion }}</strong></td>
                                        <td style="font-size: 0.8rem" class="text-muted">{{ ins.idAlumno }}</td>
                                        <td style="font-size: 0.8rem" class="text-muted">{{ ins.idMateria }}</td>
                                        <td>{{ ins.fecha }}</td>
                                        <td class="text-center">
                                            <button @click="modificarInscripcion(ins)" class="btn btn-sm btn-outline-primary me-2">✎</button>
                                            <button @click="eliminarInscripcion(ins)" class="btn btn-sm btn-outline-danger">✖</button>
                                        </td>
                                    </tr>
                                    <tr v-if="data_inscripciones.length === 0">
                                        <td colspan="5" class="text-center text-muted fst-italic py-4">No se encontraron inscripciones.</td>
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

export default inscripciones;
