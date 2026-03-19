// componentes/docentes.js
const workerDocente = new Worker('db/worker.js');

const docentes = {
    props: ['forms'],
    data() {
        return {
            docente: {
                idDocente: 0,
                codigo: "",
                nombre: "",
                direccion: "",
                email: "",
                telefono: "",
                escalafon: ""
            },
            accion: 'nuevo',
            idDocente: 0
        }
    },
    methods: {
        buscarDocente() {
            this.forms.busqueda_docentes.mostrar = !this.forms.busqueda_docentes.mostrar;
            this.$emit('buscar');
        },
        modificarDocente(docente) {
            this.accion = 'modificar';
            this.idDocente = docente.idDocente;
            this.docente = { ...docente };
        },
        guardarDocente() {
            // 1. Validar que el objeto docente tenga datos
            if(!this.docente.codigo || !this.docente.nombre) {
                alertify.warning("Por favor complete el código y nombre.");
                return;
            }

            console.log("Enviando docente al Worker...");
            
            let datos = {
                idDocente: this.accion === 'modificar' ? this.idDocente : new Date().getTime(),
                codigo: this.docente.codigo,
                nombre: this.docente.nombre,
                direccion: this.docente.direccion,
                email: this.docente.email,
                telefono: this.docente.telefono,
                escalafon: this.docente.escalafon
            };

            // 2. Enviar al worker
            workerDocente.postMessage({ type: 'GUARDAR_DOCENTE', data: datos });

            // 3. Escuchar respuesta
            workerDocente.onmessage = (e) => {
                console.log("Respuesta recibida del Worker:", e.data);
                if (e.data.type === 'SUCCESS_DOCENTE') {
                    alertify.success(`${datos.nombre} guardado correctamente`);
                    this.limpiarFormulario();
                    this.$emit('buscar'); 
                } else if (e.data.type === 'ERROR') {
                    alertify.error("Error en la base de datos: " + e.data.message);
                }
            };
        },
        limpiarFormulario() {
            this.accion = 'nuevo';
            this.idDocente = 0;
            this.docente = { idDocente: 0, codigo: '', nombre: '', direccion: '', email: '', telefono: '', escalafon: '' };
        }
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form @submit.prevent="guardarDocente" @reset.prevent="limpiarFormulario">
                    <div class="card card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center p-3">
                            <span><i class="bi bi-person-badge me-2"></i> REGISTRO DE DOCENTES</span>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label-custom">CÓDIGO:</label>
                                    <input placeholder="Ej: DOC001" required v-model="docente.codigo" type="text" class="form-control" :disabled="accion === 'modificar'">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label-custom">NOMBRE COMPLETO:</label>
                                    <input placeholder="Nombre del docente" required v-model="docente.nombre" type="text" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">DIRECCIÓN:</label>
                                    <input placeholder="Dirección de residencia" required v-model="docente.direccion" type="text" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">EMAIL:</label>
                                    <input placeholder="correo@ejemplo.com" required v-model="docente.email" type="email" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-custom">TELÉFONO:</label>
                                    <input placeholder="0000-0000" required v-model="docente.telefono" type="text" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-custom">ESCALAFÓN:</label>
                                    <select required v-model="docente.escalafon" class="form-select">
                                        <option value="tecnico">Técnico</option>
                                        <option value="profesor">Profesor</option>
                                        <option value="ingeniero">Lic./Ing.</option>
                                        <option value="maestria">Maestría</option>
                                        <option value="doctor">Doctorado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-transparent border-0 pb-4">
                            <button type="submit" class="btn btn-primary btn-custom shadow-sm px-4">GUARDAR</button>
                            <button type="reset" class="btn btn-warning btn-custom shadow-sm px-4">NUEVO</button>
                            <button type="button" @click="buscarDocente" class="btn btn-success btn-custom shadow-sm px-4">BUSCAR</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `
};