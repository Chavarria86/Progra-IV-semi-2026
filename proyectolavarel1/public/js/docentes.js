const docentes = {
    props:['forms'],
    data(){
        return{
            docente:{
                Id_Docentes:0,
                codigo:"",
                nombre:"",
                direccion:"",
                telefono:"",
                email:"",
                escalafon:""
            },
            accion:'nuevo',
            Id_Docentes:0,
            data_docentes:[],
            busqueda: ''
        }
    },
    mounted() {
        this.obtenerDocentes();
    },
    methods:{
        buscarDocente(){
            this.obtenerDocentes();
        },
        modificarDocente(docente){
            this.accion = 'modificar';
            this.Id_Docentes = docente.Id_Docentes;
            this.docente.codigo = docente.codigo;
            this.docente.nombre = docente.nombre;
            this.docente.direccion = docente.direccion;
            this.docente.telefono = docente.telefono;
            this.docente.email = docente.email;
            this.docente.escalafon = docente.escalafon;
        },
        async eliminarDocente(docente) {
            if(!confirm(`¿Estás seguro de eliminar a ${docente.nombre}?`)) return;
            try {
                let response = await fetch(`/api/docentes/${docente.Id_Docentes}`, { method: 'DELETE' });
                let data = await response.json();
                if(response.ok) {
                    if(typeof alertify !== 'undefined') alertify.success('Docente eliminado');
                    this.obtenerDocentes();
                } else {
                    if(typeof alertify !== 'undefined') alertify.error(`Error: ${data.message}`);
                }
            } catch(e) {
                if(typeof alertify !== 'undefined') alertify.error('Error al conectar con la API');
            }
        },
        async guardarDocente() {
            let datos = {
                Id_Docentes: this.accion=='modificar' ? this.Id_Docentes : this.getId(),
                codigo: this.docente.codigo,
                nombre: this.docente.nombre,
                direccion: this.docente.direccion,
                telefono: this.docente.telefono,
                email: this.docente.email,
                escalafon: this.docente.escalafon
            };
            
            let method = this.accion === 'modificar' ? 'PUT' : 'POST';
            let url = this.accion === 'modificar' ? `/api/docentes/${this.Id_Docentes}` : '/api/docentes';

            try {
                let response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos)
                });
                let data = await response.json();
                
                if(!response.ok) {
                    let errorMsg = data.message || 'Error al guardar el docente';
                    if(typeof alertify !== 'undefined') alertify.error(errorMsg);
                } else {
                    if(typeof alertify !== 'undefined') alertify.success(`${datos.nombre} guardado exitosamente`);
                    this.limpiarFormulario();
                    this.obtenerDocentes();
                }
            } catch(e) {
                if(typeof alertify !== 'undefined') alertify.error('Error de conexión con el servidor');
            }
        },
        async obtenerDocentes() {
            let url = '/api/docentes';
            if (this.busqueda.trim() !== '') url += `?buscar=${encodeURIComponent(this.busqueda)}`;
            try {
                let response = await fetch(url);
                if(response.ok) this.data_docentes = await response.json();
            } catch(e) { console.error(e); }
        },
        getId(){ return typeof uuid !== 'undefined' ? uuid.v4() : crypto.randomUUID(); },
        limpiarFormulario(){
            this.accion = 'nuevo';
            this.Id_Docentes = 0;
            this.docente.codigo = '';
            this.docente.nombre = '';
            this.docente.direccion = '';
            this.docente.telefono = '';
            this.docente.email = '';
            this.docente.escalafon = '';
        },
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">REGISTRO DE DOCENTES</h4>
                        <button type="reset" form="frmDocentes" class="btn btn-sm btn-outline-light">Nuevo</button>
                    </div>
                    <div class="card-body">
                        <form id="frmDocentes" @submit.prevent="guardarDocente" @reset.prevent="limpiarFormulario">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">CÓDIGO:</label>
                                    <input required v-model="docente.codigo" type="text" class="form-control">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">NOMBRE COMPLETO:</label>
                                    <input required v-model="docente.nombre" type="text" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">DIRECCIÓN:</label>
                                    <input required v-model="docente.direccion" type="text" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">TELÉFONO:</label>
                                    <input required v-model="docente.telefono" type="text" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">EMAIL:</label>
                                    <input required v-model="docente.email" type="email" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ESCALAFÓN:</label>
                                    <select required v-model="docente.escalafon" class="form-select">
                                        <option value="" disabled selected>-- Seleccione Escalafón --</option>
                                        <option value="tecnico">Técnico</option>
                                        <option value="profesor">Profesor</option>
                                        <option value="ingeniero">Licenciado/Ingeniero</option>
                                        <option value="maestria">Maestría</option>
                                        <option value="doctor">Doctor</option>
                                    </select>
                                </div>
                            </div>
                            <hr class="my-4">
                            <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i>
                                {{ accion === 'nuevo' ? 'GUARDAR' : 'ACTUALIZAR' }} DOCENTE
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white"><h4 class="mb-0">LISTADO DE DOCENTES</h4></div>
                    <div class="card-body">
                        <div class="input-group mb-4">
                            <span class="input-group-text bg-light">🔍</span>
                            <input placeholder="Buscar docentes..." v-model="busqueda" @keyup="buscarDocente" type="text" class="form-control">
                            <button @click="buscarDocente" class="btn btn-secondary">Buscar</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light"><tr><th>CÓDIGO</th><th>NOMBRE</th><th>ESCALAFÓN</th><th>ACCIONES</th></tr></thead>
                                <tbody>
                                    <tr v-for="doce in data_docentes" :key="doce.Id_Docentes" :class="Id_Docentes === doce.Id_Docentes ? 'table-primary' : ''">
                                        <td><strong>{{ doce.codigo }}</strong></td><td>{{ doce.nombre }}</td><td>{{ doce.escalafon }}</td>
                                        <td class="text-center">
                                            <button @click="modificarDocente(doce)" class="btn btn-sm btn-outline-primary me-2">Editar</button>
                                            <button @click="eliminarDocente(doce)" class="btn btn-sm btn-outline-danger">Eliminar</button>
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
export default docentes;
