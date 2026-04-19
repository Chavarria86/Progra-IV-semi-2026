const alumnos = {
    props:['forms'],
    data(){
        return{
            alumno:{
                idAlumno:0,
                codigo:"",
                nombre:"",
                direccion:"",
                email:"",
                telefono:""
            },
            accion:'nuevo',
            idAlumno:0,
            data_alumnos:[],
            busqueda: ''
        }
    },
    mounted() {
        // Al montar el componente obtenemos todos
        this.obtenerAlumnos();
    },
    methods:{
        cerrarFormularioAlumno(){
            if(this.forms && this.forms.alumnos) {
                this.forms.alumnos.mostrar = false;
            }
        },
        buscarAlumno(){
            this.obtenerAlumnos();
        },
        modificarAlumno(alumno){
            this.accion = 'modificar';
            this.idAlumno = alumno.idAlumno;
            this.alumno.codigo = alumno.codigo;
            this.alumno.nombre = alumno.nombre;
            this.alumno.direccion = alumno.direccion;
            this.alumno.email = alumno.email;
            this.alumno.telefono = alumno.telefono;
        },
        async eliminarAlumno(alumno) {
            if(!confirm(`¿Estás seguro de eliminar a ${alumno.nombre}?`)) return;
            
            try {
                let response = await fetch(`/api/alumnos/${alumno.idAlumno}`, {
                    method: 'DELETE'
                });
                
                let data = await response.json();
                if(response.ok) {
                    if(typeof alertify !== 'undefined') alertify.success('Alumno eliminado');
                    this.obtenerAlumnos();
                } else {
                    if(typeof alertify !== 'undefined') alertify.error(`Error: ${data.message}`);
                }
            } catch(e) {
                if(typeof alertify !== 'undefined') alertify.error('Error al conectar con la API');
            }
        },
        async guardarAlumno() {
            let datos = {
                idAlumno: this.accion=='modificar' ? this.idAlumno : this.getId(),
                codigo: this.alumno.codigo,
                nombre: this.alumno.nombre,
                direccion: this.alumno.direccion,
                email: this.alumno.email,
                telefono: this.alumno.telefono
            };
            
            // Validate frontend existing codigo
            if(this.data_alumnos.some(a => a.codigo === datos.codigo) && this.accion=='nuevo'){
                if(typeof alertify !== 'undefined') alertify.warning(`El codigo del alumno ya está en uso.`);
                return; 
            }

            // Simulate Dexie offline if db exists
            if(typeof db !== 'undefined' && db.alumnos) {
                db.alumnos.put(datos);
            }

            // Determine correct REST URL and Method
            let method = this.accion === 'modificar' ? 'PUT' : 'POST';
            let url = this.accion === 'modificar' ? `/api/alumnos/${this.idAlumno}` : '/api/alumnos';

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
                    console.error("Backend error:", data);
                } else {
                    if(typeof alertify !== 'undefined') alertify.success(`${datos.nombre} procesado correctamente`);
                    this.limpiarFormulario();
                    this.obtenerAlumnos();
                }
            } catch(e) {
                console.error("Fetch Error:", e);
                if(typeof alertify !== 'undefined') alertify.error('Error al conectar con la API de Laravel');
            }
        },
        async obtenerAlumnos() {
            let url = '/api/alumnos';
            if (this.busqueda.trim() !== '') {
                url += `?buscar=${encodeURIComponent(this.busqueda)}`;
            }
            try {
                let response = await fetch(url);
                if(response.ok) {
                    this.data_alumnos = await response.json();
                }
            } catch(e) {
                console.error("Fetch Error:", e);
            }
        },
        getId(){
            return typeof uuid !== 'undefined' ? uuid.v4() : crypto.randomUUID();
        },
        limpiarFormulario(){
            this.accion = 'nuevo';
            this.idAlumno = 0;
            this.alumno.codigo = '';
            this.alumno.nombre = '';
            this.alumno.direccion = '';
            this.alumno.email = '';
            this.alumno.telefono = '';
        },
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <!-- CARD FORMULARIO -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-person-fill"></i> REGISTRO DE ALUMNOS</h4>
                        <button type="reset" form="frmAlumnos" class="btn btn-sm btn-outline-light">Nuevo / Limpiar</button>
                    </div>
                    
                    <div class="card-body">
                        <form id="frmAlumnos" @submit.prevent="guardarAlumno" @reset.prevent="limpiarFormulario">
                            <div class="row g-3">
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">CÓDIGO:</label>
                                    <input placeholder="Ej: AL-001" required v-model="alumno.codigo" type="text" class="form-control" autocomplete="off">
                                </div>
                                
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">NOMBRE COMPLETO:</label>
                                    <input placeholder="Nombre completo" required v-model="alumno.nombre" type="text" class="form-control" autocomplete="off">
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">DIRECCIÓN:</label>
                                    <input placeholder="Dirección domiciliar" required v-model="alumno.direccion" type="text" class="form-control" autocomplete="off">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">EMAIL:</label>
                                    <input placeholder="correo@ejemplo.com" required v-model="alumno.email" type="email" class="form-control" autocomplete="off">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">TELÉFONO:</label>
                                    <input placeholder="0000-0000" required v-model="alumno.telefono" type="text" class="form-control" autocomplete="off">
                                </div>
                                
                            </div>
                            
                            <hr class="my-4">
                            
                            <button type="submit" id="btnGuardarAlumno" class="btn btn-success w-100 fw-bold py-2">
                                {{ accion === 'nuevo' ? 'GUARDAR ALUMNO' : 'ACTUALIZAR ALUMNO' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- CARD TABLA Y BÚSQUEDA -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">LISTA VIVA DE ALUMNOS</h4>
                    </div>
                    
                    <div class="card-body">
                        <div class="input-group mb-4">
                            <span class="input-group-text bg-light">🔍</span>
                            <input placeholder="Buscar por código, nombre, email o teléfono..." v-model="busqueda" @keyup="buscarAlumno" type="text" class="form-control">
                            <button type="button" @click="buscarAlumno" class="btn btn-secondary px-4">Buscar</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>CÓDIGO</th>
                                        <th>NOMBRE</th>
                                        <th>DIRECCIÓN</th>
                                        <th>TELÉFONO</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="al in data_alumnos" :key="al.idAlumno" :class="idAlumno === al.idAlumno ? 'table-primary' : ''">
                                        <td><strong>{{ al.codigo }}</strong></td>
                                        <td>{{ al.nombre }}</td>
                                        <td>{{ al.direccion }}</td>
                                        <td>{{ al.telefono }}</td>
                                        <td class="text-center">
                                            <button @click="modificarAlumno(al)" class="btn btn-sm btn-outline-primary me-2">✎ Editar</button>
                                            <button @click="eliminarAlumno(al)" class="btn btn-sm btn-outline-danger">✖ Eliminar</button>
                                        </td>
                                    </tr>
                                    <tr v-if="data_alumnos.length === 0">
                                        <td colspan="5" class="text-center text-muted fst-italic py-4">No se encontraron registros.</td>
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

export default alumnos;
