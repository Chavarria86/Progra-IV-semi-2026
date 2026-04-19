const materias = {
    props:['forms'],
    data(){
        return{
            materia:{
                idMateria:0,
                codigo:"",
                nombre:"",
                uv:""
            },
            accion:'nuevo',
            idMateria:0,
            data_materias:[],
            busqueda: ''
        }
    },
    mounted() {
        this.obtenerMaterias();
    },
    methods:{
        buscarMateria(){ this.obtenerMaterias(); },
        modificarMateria(materia){
            this.accion = 'modificar';
            this.idMateria = materia.idMateria;
            this.materia.codigo = materia.codigo;
            this.materia.nombre = materia.nombre;
            this.materia.uv = materia.uv;
        },
        async eliminarMateria(materia) {
            if(!confirm(`¿Eliminar materia ${materia.nombre}?`)) return;
            let response = await fetch(`/api/materias/${materia.idMateria}`, { method: 'DELETE' });
            if(response.ok) {
                if(typeof alertify !== 'undefined') alertify.success('Materia eliminada');
                this.obtenerMaterias();
            }
        },
        async guardarMateria() {
            let datos = {
                idMateria: this.accion=='modificar' ? this.idMateria : this.getId(),
                codigo: this.materia.codigo,
                nombre: this.materia.nombre,
                uv: this.materia.uv
            };
            let method = this.accion === 'modificar' ? 'PUT' : 'POST';
            let url = this.accion === 'modificar' ? `/api/materias/${this.idMateria}` : '/api/materias';
            let response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos)
            });
            if(response.ok) {
                if(typeof alertify !== 'undefined') alertify.success('Materia procesada correctamente');
                this.limpiarFormulario();
                this.obtenerMaterias();
            }
        },
        async obtenerMaterias() {
            let url = '/api/materias' + (this.busqueda.trim() ? `?buscar=${encodeURIComponent(this.busqueda)}` : '');
            let response = await fetch(url);
            if(response.ok) this.data_materias = await response.json();
        },
        getId(){ return typeof uuid !== 'undefined' ? uuid.v4() : crypto.randomUUID(); },
        limpiarFormulario(){
            this.accion = 'nuevo';
            this.idMateria = 0;
            this.materia.codigo = '';
            this.materia.nombre = '';
            this.materia.uv = '';
        },
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">REGISTRO DE MATERIAS</h4>
                        <button type="reset" form="frmMateria" class="btn btn-sm btn-outline-light">Nuevo</button>
                    </div>
                    <div class="card-body">
                        <form id="frmMateria" @submit.prevent="guardarMateria" @reset.prevent="limpiarFormulario">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">CÓDIGO:</label>
                                    <input required v-model="materia.codigo" type="text" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">NOMBRE DE LA MATERIA:</label>
                                    <input required v-model="materia.nombre" type="text" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">UV:</label>
                                    <input required v-model="materia.uv" type="number" class="form-control">
                                </div>
                            </div>
                            <hr class="my-4">
                            <button type="submit" class="btn btn-success w-100 fw-bold">{{ accion === 'nuevo' ? 'GUARDAR' : 'ACTUALIZAR' }} MATERIA</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white"><h4 class="mb-0">LISTADO DE MATERIAS</h4></div>
                    <div class="card-body">
                        <div class="input-group mb-4">
                            <input placeholder="Buscar por código u nombre..." v-model="busqueda" @keyup="buscarMateria" type="text" class="form-control">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light"><tr><th>CÓDIGO</th><th>NOMBRE</th><th>UV</th><th>ACCIONES</th></tr></thead>
                                <tbody>
                                    <tr v-for="mat in data_materias" :key="mat.idMateria">
                                        <td><strong>{{ mat.codigo }}</strong></td><td>{{ mat.nombre }}</td><td>{{ mat.uv }}</td>
                                        <td class="text-center">
                                            <button @click="modificarMateria(mat)" class="btn btn-sm btn-outline-primary me-2">Editar</button>
                                            <button @click="eliminarMateria(mat)" class="btn btn-sm btn-outline-danger">Eliminar</button>
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
export default materias;
