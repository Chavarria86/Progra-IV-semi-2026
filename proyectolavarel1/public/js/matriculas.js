const matriculas = {
    props:['forms'],
    data(){
        return{
            matricula:{
                idMatricula:0, idAlumno:"", idMateria:"", idDocente:"",
                fecha:"", estado:"ACTIVO", periodo:"", gestion:(new Date()).getFullYear()
            },
            accion:'nuevo',
            idMatricula:0,
            data_matriculas:[],
            data_alumnos:[], data_materias:[], data_docentes:[],
            busqueda: ''
        }
    },
    mounted() {
        this.obtenerMatriculas();
        this.cargarSelects();
    },
    methods:{
        async cargarSelects() {
            let resAlum = await fetch('/api/alumnos'); if(resAlum.ok) this.data_alumnos = await resAlum.json();
            let resMat = await fetch('/api/materias'); if(resMat.ok) this.data_materias = await resMat.json();
            let resDoc = await fetch('/api/docentes'); if(resDoc.ok) this.data_docentes = await resDoc.json();
        },
        buscarMatricula(){ this.obtenerMatriculas(); },
        modificarMatricula(mat){
            this.accion = 'modificar';
            this.idMatricula = mat.idMatricula;
            this.matricula.idAlumno = mat.idAlumno;
            this.matricula.idMateria = mat.idMateria;
            this.matricula.idDocente = mat.idDocente;
            this.matricula.fecha = mat.fecha;
            this.matricula.estado = mat.estado;
            this.matricula.periodo = mat.periodo;
            this.matricula.gestion = mat.gestion;
        },
        async eliminarMatricula(mat) {
            if(!confirm(`¿Eliminar matrícula?`)) return;
            let response = await fetch(`/api/matriculas/${mat.idMatricula}`, { method: 'DELETE' });
            if(response.ok) {
                if(typeof alertify !== 'undefined') alertify.success('Matrícula eliminada');
                this.obtenerMatriculas();
            }
        },
        async guardarMatricula() {
            let datos = {
                idMatricula: this.accion=='modificar' ? this.idMatricula : this.getId(),
                ...this.matricula
            };
            let method = this.accion === 'modificar' ? 'PUT' : 'POST';
            let url = this.accion === 'modificar' ? `/api/matriculas/${this.idMatricula}` : '/api/matriculas';
            let response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos)
            });
            if(response.ok) {
                if(typeof alertify !== 'undefined') alertify.success('Matrícula guardada exitosamente');
                this.limpiarFormulario();
                this.obtenerMatriculas();
            }
        },
        async obtenerMatriculas() {
            let url = '/api/matriculas' + (this.busqueda.trim() ? `?buscar=${encodeURIComponent(this.busqueda)}` : '');
            let response = await fetch(url);
            if(response.ok) this.data_matriculas = await response.json();
        },
        getId(){ return typeof uuid !== 'undefined' ? uuid.v4() : crypto.randomUUID(); },
        limpiarFormulario(){
            this.accion = 'nuevo';
            this.idMatricula = 0;
            this.matricula.idAlumno = '';
            this.matricula.idMateria = '';
            this.matricula.idDocente = '';
            this.matricula.fecha = '';
            this.matricula.estado = 'ACTIVO';
            this.matricula.periodo = '';
            this.matricula.gestion = (new Date()).getFullYear();
        },
        getLabel(collection, idKey, searchId, returnKey) {
            let item = collection.find(x => x[idKey] == searchId);
            return item ? item[returnKey] : 'Desconocido';
        }
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">REGISTRO DE MATRÍCULAS</h4>
                        <button type="reset" form="frmMatricula" class="btn btn-sm btn-outline-light">Nuevo</button>
                    </div>
                    <div class="card-body">
                        <form id="frmMatricula" @submit.prevent="guardarMatricula" @reset.prevent="limpiarFormulario">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ALUMNO:</label>
                                    <select required v-model="matricula.idAlumno" class="form-control">
                                        <option value="" disabled>Seleccione Alumno</option>
                                        <option v-for="a in data_alumnos" :value="a.idAlumno">{{ a.codigo }} - {{ a.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">MATERIA:</label>
                                    <select required v-model="matricula.idMateria" class="form-control">
                                        <option value="" disabled>Seleccione Materia</option>
                                        <option v-for="m in data_materias" :value="m.idMateria">{{ m.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">DOCENTE:</label>
                                    <select required v-model="matricula.idDocente" class="form-control">
                                        <option value="" disabled>Seleccione Docente</option>
                                        <option v-for="d in data_docentes" :value="d.Id_Docentes">{{ d.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">FECHA:</label>
                                    <input required v-model="matricula.fecha" type="date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">PERIODO:</label>
                                    <input required v-model="matricula.periodo" type="text" placeholder="Ej: Ciclo 1" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">GESTIÓN (AÑO):</label>
                                    <input required v-model="matricula.gestion" type="number" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">ESTADO:</label>
                                    <select required v-model="matricula.estado" class="form-control">
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                    </select>
                                </div>
                            </div>
                            <hr class="my-4">
                            <button type="submit" class="btn btn-success w-100 fw-bold">{{ accion === 'nuevo' ? 'GUARDAR' : 'ACTUALIZAR' }} MATRÍCULA</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white"><h4 class="mb-0">MATRÍCULAS ACTIVAS</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light"><tr><th>FECHA</th><th>ALUMNO</th><th>MATERIA</th><th>PERIODO</th><th>ESTADO</th><th>ACCIONES</th></tr></thead>
                                <tbody>
                                    <tr v-for="mat in data_matriculas" :key="mat.idMatricula">
                                        <td>{{ mat.fecha }}</td>
                                        <td>{{ getLabel(data_alumnos, 'idAlumno', mat.idAlumno, 'nombre') }}</td>
                                        <td>{{ getLabel(data_materias, 'idMateria', mat.idMateria, 'nombre') }}</td>
                                        <td>{{ mat.periodo }} / {{ mat.gestion }}</td>
                                        <td><span class="badge" :class="mat.estado=='ACTIVO' ? 'bg-success' : 'bg-danger'">{{ mat.estado }}</span></td>
                                        <td class="text-center">
                                            <button @click="modificarMatricula(mat)" class="btn btn-sm btn-outline-primary">Editar</button>
                                            <button @click="eliminarMatricula(mat)" class="btn btn-sm btn-outline-danger">Eliminar</button>
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
export default matriculas;
